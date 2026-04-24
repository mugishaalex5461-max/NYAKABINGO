<?php
require_once dirname(__DIR__) . '/includes/gallery_admin_auth.php';
gallery_admin_bootstrap();

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: 0');
}

$auth_error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['gallery_auth_action'])) {
    $auth_action = (string) $_POST['gallery_auth_action'];

    if ($auth_action === 'login') {
        // Start every login attempt from a clean state.
        gallery_admin_logout();

        $email_input = trim((string) ($_POST['admin_email'] ?? ''));
        $password_input = (string) ($_POST['admin_password'] ?? '');

        if (gallery_admin_login($email_input, $password_input)) {
            // Keep user on current POST response; next GET requires fresh login again.
        } else {
            $auth_error = 'Invalid admin credentials.';
        }
    } elseif ($auth_action === 'logout') {
        gallery_admin_logout();
    }
}

$is_gallery_admin = gallery_is_admin();
$admin_mode_requested = isset($_GET['admin']) && (string) $_GET['admin'] === '1';
$show_admin_panel = $is_gallery_admin || $admin_mode_requested;

$asset_version = (string) max(
    @filemtime(dirname(__DIR__) . '/js/upload.js') ?: 0,
    @filemtime(dirname(__DIR__) . '/js/media_upload.js') ?: 0,
    @filemtime(dirname(__DIR__) . '/js/manage_uploads.js') ?: 0,
    @filemtime(__FILE__) ?: 0
);

include '../includes/header.php';

// Gallery path helpers
$base_url = '/NYAKABINGO_PRIMARY';
$upload_dir = dirname(__DIR__) . '/images/uploads/';
$upload_url = $base_url . '/images/uploads/';

// Build gallery entries with reliable metadata
$gallery_images = [];
if (is_dir($upload_dir)) {
    $files = scandir($upload_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        // Skip generated thumbnails and non-image files
        if (strpos($file, 'thumb_') === 0 || !preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            continue;
        }

        $full_path = $upload_dir . $file;
        if (!is_file($full_path)) {
            continue;
        }

        $thumb_name = 'thumb_' . $file;
        $thumb_path = $upload_dir . $thumb_name;
        $has_thumb = is_file($thumb_path);
        $timestamp = filemtime($full_path);
        if ($timestamp === false) {
            $timestamp = time();
        }

        $gallery_images[] = [
            'name' => $file,
            'image_url' => $upload_url . rawurlencode($file),
            'thumb_url' => $has_thumb ? $upload_url . rawurlencode($thumb_name) : $upload_url . rawurlencode($file),
            'uploaded_at' => $timestamp,
        ];
    }
}

usort($gallery_images, static function ($a, $b) {
    return $b['uploaded_at'] <=> $a['uploaded_at'];
});

$images_count = count($gallery_images);
$initial_visible_count = 12;

function collect_media_files($directory, $public_url, $extensions, $preferredType = '')
{
    $items = [];
    if (!is_dir($directory)) {
        return $items;
    }

    $files = scandir($directory);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $full_path = $directory . $file;
        if (!is_file($full_path)) {
            continue;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($extension, $extensions, true)) {
            continue;
        }

        $modified = filemtime($full_path);
        if ($modified === false) {
            $modified = time();
        }

        $items[] = [
            'name' => $file,
            'url' => $public_url . rawurlencode($file),
            'ext' => $extension,
            'mime' => get_media_mime($extension, $preferredType),
            'modified' => $modified,
        ];
    }

    usort($items, static function ($a, $b) {
        return $b['modified'] <=> $a['modified'];
    });

    return $items;
}

function get_media_mime($extension, $preferredType = '')
{
    if (strtolower((string) $extension) === 'ogg') {
        return strtolower((string) $preferredType) === 'audio' ? 'audio/ogg' : 'video/ogg';
    }

    $map = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mov' => 'video/quicktime',
        'm4v' => 'video/x-m4v',
        'avi' => 'video/x-msvideo',
        'mkv' => 'video/x-matroska',
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        '3gp' => 'video/3gpp',
        'mp3' => 'audio/mpeg',
        'mpga' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'm4a' => 'audio/mp4',
        'aac' => 'audio/aac',
        'flac' => 'audio/flac',
        'opus' => 'audio/ogg; codecs=opus',
        'amr' => 'audio/amr',
        'wma' => 'audio/x-ms-wma',
    ];

    return $map[strtolower((string) $extension)] ?? '';
}

function merge_media_items($primary, $fallback)
{
    $merged = $primary;
    $seen = [];

    foreach ($primary as $item) {
        $key = strtolower((string) ($item['name'] ?? '')) . '|' . strtolower((string) ($item['url'] ?? ''));
        $seen[$key] = true;
    }

    foreach ($fallback as $item) {
        $key = strtolower((string) ($item['name'] ?? '')) . '|' . strtolower((string) ($item['url'] ?? ''));
        if (!isset($seen[$key])) {
            $merged[] = $item;
            $seen[$key] = true;
        }
    }

    usort($merged, static function ($a, $b) {
        return (($b['modified'] ?? 0) <=> ($a['modified'] ?? 0));
    });

    return $merged;
}

$media_dir = dirname(__DIR__) . '/media/';
$media_url = $base_url . '/media/';
$uploads_media_dir = dirname(__DIR__) . '/images/uploads/';
$uploads_media_url = $base_url . '/images/uploads/';

$media_directory_status = [
    'videos' => [
        'path' => $media_dir . 'videos/',
    ],
    'songs' => [
        'path' => $media_dir . 'songs/',
    ],
    'short_videos' => [
        'path' => $media_dir . 'short_videos/',
    ],
];

foreach ($media_directory_status as $key => $status) {
    $path = $status['path'];
    $media_directory_status[$key]['exists'] = is_dir($path);
    $media_directory_status[$key]['writable'] = is_dir($path) ? is_writable($path) : is_writable(dirname(rtrim($path, '/')));
}

$video_items = collect_media_files(
    $media_dir . 'videos/',
    $media_url . 'videos/',
    ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv', 'mpg', 'mpeg', '3gp'],
    'video'
);

$video_upload_fallback_items = collect_media_files(
    $uploads_media_dir,
    $uploads_media_url,
    ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv', 'mpg', 'mpeg', '3gp'],
    'video'
);

$video_items = merge_media_items($video_items, $video_upload_fallback_items);

$short_video_items = collect_media_files(
    $media_dir . 'short_videos/',
    $media_url . 'short_videos/',
    ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv', 'mpg', 'mpeg', '3gp'],
    'video'
);

$short_video_upload_fallback_items = collect_media_files(
    $uploads_media_dir,
    $uploads_media_url,
    ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv', 'mpg', 'mpeg', '3gp'],
    'video'
);

$short_video_items = merge_media_items($short_video_items, $short_video_upload_fallback_items);

$song_items = collect_media_files(
    $media_dir . 'songs/',
    $media_url . 'songs/',
    ['mp3', 'mpga', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'opus', 'amr', 'wma'],
    'audio'
);

$song_upload_fallback_items = collect_media_files(
    $uploads_media_dir,
    $uploads_media_url,
    ['mp3', 'mpga', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'opus', 'amr', 'wma'],
    'audio'
);

$song_items = merge_media_items($song_items, $song_upload_fallback_items);

$gallery_header_candidates = [
    [
        'path' => dirname(__DIR__) . '/images/uploads/1d.jpg',
        'url' => $base_url . '/images/uploads/1d.jpg',
    ],
    [
        'path' => dirname(__DIR__) . '/images/uploads/school_bag.jpg',
        'url' => $base_url . '/images/uploads/school_bag.jpg',
    ],
    [
        'path' => dirname(__DIR__) . '/images/gallery_header.jpg',
        'url' => $base_url . '/images/gallery_header.jpg',
    ],
    [
        'path' => dirname(__DIR__) . '/images/category_students.jpg',
        'url' => $base_url . '/images/category_students.jpg',
    ],
];

$gallery_header_url = $base_url . '/images/category_students.jpg';
$gallery_header_version = time();

foreach ($gallery_header_candidates as $candidate) {
    if (file_exists($candidate['path'])) {
        $gallery_header_url = $candidate['url'];
        $gallery_header_version = filemtime($candidate['path']);
        if ($gallery_header_version === false) {
            $gallery_header_version = time();
        }
        break;
    }
}

$gallery_header_full_url = $gallery_header_url . '?v=' . $gallery_header_version;
?>

<style>
    .page-header {
        position: relative;
        overflow: hidden;
        background:
            linear-gradient(135deg, rgba(30, 58, 138, 0.55) 0%, rgba(59, 130, 246, 0.45) 100%),
            var(--gallery-header-bg) center/cover no-repeat;
        color: white;
        min-height: 360px;
        padding: 40px 20px;
        text-align: center;
        border-bottom: 3px solid rgba(255, 255, 255, 0.25);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .page-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 22% 20%, rgba(255, 255, 255, 0.22), transparent 45%);
        pointer-events: none;
    }

    .page-header::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(rgba(15, 23, 42, 0.2), rgba(30, 58, 138, 0.25));
        pointer-events: none;
    }
    
    .page-header h1 {
        position: relative;
        z-index: 3;
        font-size: 40px;
        margin-bottom: 10px;
        text-shadow: 0 4px 12px rgba(0, 0, 0, 0.28);
        animation: galleryTitleFloat 3.2s ease-in-out infinite;
    }
    
    .page-header p {
        position: relative;
        z-index: 3;
        font-size: 18px;
        opacity: 0.95;
        text-shadow: 0 3px 8px rgba(0, 0, 0, 0.22);
        animation: gallerySubtitleFloat 3.2s ease-in-out infinite;
        animation-delay: 0.25s;
    }

    @keyframes galleryTitleFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    @keyframes gallerySubtitleFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    
    .page-content {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .gallery-info {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .gallery-info h2 {
        color: #1e3a8a;
        font-size: 32px;
        margin-bottom: 15px;
    }
    
    .gallery-info p {
        color: #666;
        font-size: 16px;
        line-height: 1.6;
        max-width: 700px;
        margin: 0 auto;
    }
    
    /* Upload Section */
    .upload-section {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border: 2px solid #0ea5e9;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 50px;
    }
    
    .upload-section h3 {
        color: #1e3a8a;
        margin-bottom: 20px;
        font-size: 22px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .upload-form {
        background: white;
        padding: 20px;
        border-radius: 8px;
    }
    
    .drag-drop-zone {
        border: 2px dashed #3b82f6;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        background: #f9fafb;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 20px;
    }
    
    .drag-drop-zone:hover {
        background: #eff6ff;
        border-color: #0ea5e9;
    }
    
    .drag-drop-zone p {
        color: #666;
        margin: 0;
        font-size: 16px;
    }
    
    .drag-drop-zone .icon {
        font-size: 40px;
        margin-bottom: 10px;
    }
    
    #image-input {
        display: none;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        color: #1e3a8a;
        font-weight: bold;
        margin-bottom: 8px;
    }
    
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        font-family: inherit;
    }
    
    #image-preview {
        margin: 15px 0;
    }
    
    #image-preview img {
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    #upload-status {
        display: none;
        padding: 12px 15px;
        border-radius: 5px;
        border-left: 4px solid;
        margin-bottom: 15px;
    }
    
    .upload-btn {
        background: linear-gradient(135deg, #3b82f6, #1e3a8a);
        color: white;
        padding: 10px 25px;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s;
        font-size: 14px;
    }
    
    .upload-btn:hover:not(:disabled) {
        transform: translateY(-2px);
    }
    
    .upload-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .auth-box {
        background: #ffffff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 18px;
        margin-top: 16px;
        display: none;
    }

    .auth-box.show {
        display: block;
    }

    .auth-box h4 {
        color: #1e3a8a;
        margin-bottom: 10px;
        font-size: 18px;
    }

    .auth-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px;
        margin-bottom: 10px;
    }

    .auth-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
    }

    .auth-error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
        border-radius: 6px;
        padding: 8px 10px;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .admin-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #dcfce7;
        color: #14532d;
        border: 1px solid #86efac;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 700;
    }

    .media-upload-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 18px;
        margin-top: 20px;
    }

    .quick-upload-panel {
        margin-top: 18px;
        background: #ffffff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 16px;
    }

    .quick-upload-panel h4 {
        color: #1e3a8a;
        margin-bottom: 6px;
        font-size: 17px;
    }

    .quick-upload-panel p {
        color: #475569;
        font-size: 13px;
        margin-bottom: 12px;
    }

    .quick-upload-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }

    .quick-upload-btn {
        border: 1px solid #93c5fd;
        background: #eff6ff;
        color: #1e3a8a;
        border-radius: 8px;
        padding: 9px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-align: left;
        transition: all 0.2s;
    }

    .quick-upload-btn:hover {
        background: #dbeafe;
        border-color: #3b82f6;
    }

    .media-upload-card {
        background: #ffffff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 16px;
    }

    .media-upload-card h4 {
        color: #1e3a8a;
        margin-bottom: 12px;
        font-size: 18px;
    }

    .media-upload-form .media-drop-zone {
        border: 2px dashed #3b82f6;
        border-radius: 8px;
        padding: 22px 12px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.25s;
        margin-bottom: 12px;
    }

    .media-upload-form .media-drop-zone:hover {
        background: #eff6ff;
        border-color: #1d4ed8;
    }

    .media-upload-form input[type="file"] {
        display: none;
    }

    .media-upload-status {
        display: none;
        padding: 10px 12px;
        border-left: 4px solid;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 12px;
    }

    .media-selected-file {
        font-size: 13px;
        color: #334155;
        margin-bottom: 10px;
        min-height: 18px;
    }

    .media-upload-btn {
        width: 100%;
        background: linear-gradient(135deg, #2563eb, #1e40af);
        border: none;
        border-radius: 6px;
        color: #fff;
        padding: 10px 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .media-upload-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .upload-diagnostics {
        margin-top: 12px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 12px;
        color: #334155;
    }

    .diag-ok {
        color: #166534;
        font-weight: 700;
    }

    .diag-bad {
        color: #991b1b;
        font-weight: 700;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin: 40px 0;
    }
    
    .gallery-item {
        background: linear-gradient(135deg, #3b82f6, #1e3a8a);
        border-radius: 10px;
        overflow: hidden;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .item-manage {
        position: absolute;
        top: 8px;
        left: 8px;
        right: 8px;
        display: flex;
        gap: 6px;
        justify-content: flex-end;
        z-index: 3;
    }

    .mini-btn {
        border: none;
        border-radius: 6px;
        padding: 5px 8px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        cursor: pointer;
        background: rgba(15, 23, 42, 0.8);
    }

    .mini-btn:hover {
        background: rgba(30, 58, 138, 0.95);
    }

    .mini-btn.delete {
        background: rgba(185, 28, 28, 0.9);
    }

    .mini-btn.delete:hover {
        background: rgba(153, 27, 27, 1);
    }
    
    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .gallery-item.placeholder {
        font-size: 80px;
        background: linear-gradient(135deg, #3b82f6, #1e3a8a);
    }
    
    .gallery-item:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 20px rgba(59, 130, 246, 0.4);
    }
    
    .gallery-item-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 15px;
        text-align: center;
        font-size: 16px;
        font-weight: bold;
        transform: translateY(100%);
        transition: transform 0.3s;
    }
    
    .gallery-item:hover .gallery-item-label {
        transform: translateY(0);
    }
    
    .gallery-categories {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin: 40px 0;
    }
    
    .category-section {
        position: relative;
        width: 100%;
        aspect-ratio: 1;
        border-radius: 50%;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .category-section:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4);
    }
    
    .category-background {
        position: absolute;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        border-radius: 50%;
    }
    
    /* Category Background Images - Using Actual Images with Overlay */
    .category-students .category-background {
        background-image: url('/NYAKABINGO_PRIMARY/images/category_students.jpg');
        background-size: cover;
        background-position: center;
    }
    
    .category-students .category-background::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(30, 58, 138, 0.3);
        border-radius: 50%;
    }
    
    .category-sports .category-background {
        background-image: url('/NYAKABINGO_PRIMARY/images/category_sports.jpg');
        background-size: cover;
        background-position: center;
    }
    
    .category-sports .category-background::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(194, 65, 12, 0.3);
        border-radius: 50%;
    }
    
    .category-celebrations .category-background {
        background-image: url('/NYAKABINGO_PRIMARY/images/category_celebrations.jpg');
        background-size: cover;
        background-position: center;
    }
    
    .category-celebrations .category-background::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(126, 34, 206, 0.3);
        border-radius: 50%;
    }
    
    .category-creative .category-background {
        background-image: url('/NYAKABINGO_PRIMARY/images/category_creative.jpg');
        background-size: cover;
        background-position: center;
    }
    
    .category-creative .category-background::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(190, 24, 93, 0.3);
        border-radius: 50%;
    }
    
    .category-overlay {
        position: absolute;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        z-index: 2;
    }
    
    .category-overlay h3 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .category-overlay p {
        font-size: 14px;
        opacity: 0.9;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        line-height: 1.4;
    }
    
    @media (max-width: 768px) {
        .category-section {
            aspect-ratio: 1;
            margin: 0 auto;
            max-width: 250px;
        }
        
        .category-overlay h3 {
            font-size: 20px;
        }
        
        .category-overlay p {
            font-size: 12px;
        }
        
    }
    
    .note-box {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 20px;
        border-radius: 5px;
        margin-top: 30px;
        text-align: center;
    }
    
    .note-box p {
        color: #1e3a8a;
        font-size: 16px;
    }
    
    .no-images {
        text-align: center;
        padding: 40px 20px;
        background: #f9fafb;
        border-radius: 10px;
        color: #666;
    }
    
    .no-images p {
        font-size: 18px;
        margin-bottom: 10px;
    }
    
    .images-count {
        background: #eff6ff;
        padding: 10px 15px;
        border-radius: 5px;
        color: #1e3a8a;
        font-weight: bold;
        display: inline-block;
        margin-top: 20px;
    }

    .gallery-actions {
        text-align: center;
        margin-top: 20px;
    }

    .load-more-btn {
        background: #1e3a8a;
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 10px 24px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
    }

    .load-more-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

    .load-more-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    .media-section {
        margin-top: 60px;
    }

    .media-title {
        color: #1e3a8a;
        font-size: 28px;
        text-align: center;
        margin-bottom: 12px;
    }

    .media-subtitle {
        color: #64748b;
        text-align: center;
        margin-bottom: 28px;
        font-size: 15px;
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .media-card {
        background: #ffffff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 8px 20px rgba(30, 58, 138, 0.08);
    }

    .media-card h4 {
        color: #1e3a8a;
        font-size: 20px;
        margin-bottom: 10px;
    }

    .media-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .media-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px;
    }

    .media-item video,
    .media-item audio {
        width: 100%;
        display: block;
        border-radius: 8px;
    }

    .media-links {
        margin-top: 8px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .media-link {
        display: inline-block;
        background: #1e3a8a;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .media-link:hover {
        background: #1d4ed8;
    }

    .media-play-all-wrap {
        text-align: center;
        margin-bottom: 20px;
    }

    .media-player-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(2, 6, 23, 0.9);
        z-index: 1200;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .media-player-shell {
        width: min(900px, 96vw);
        background: #0f172a;
        color: #fff;
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 14px;
        padding: 16px;
        position: relative;
    }

    .media-player-close {
        position: absolute;
        right: 16px;
        top: 10px;
        border: none;
        background: transparent;
        color: #fff;
        font-size: 30px;
        cursor: pointer;
    }

    .media-player-title {
        font-size: 18px;
        margin-bottom: 4px;
        padding-right: 36px;
        word-break: break-word;
    }

    .media-player-meta {
        color: #94a3b8;
        font-size: 13px;
        margin-bottom: 12px;
    }

    .media-player-stage video,
    .media-player-stage audio {
        width: 100%;
        border-radius: 10px;
        background: #020617;
    }

    .media-player-controls {
        margin-top: 12px;
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .media-name {
        margin-top: 8px;
        font-size: 13px;
        color: #334155;
        word-break: break-word;
    }

    .media-manage {
        margin-top: 8px;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .media-manage .mini-btn {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 6px;
    }

    .media-empty {
        background: #eff6ff;
        border: 1px dashed #93c5fd;
        color: #1e3a8a;
        border-radius: 10px;
        padding: 16px;
        font-size: 14px;
    }

    .media-inline-upload {
        margin-bottom: 14px;
        background: #f8fafc;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        padding: 12px;
    }

    .media-login-state {
        font-size: 12px;
        margin-bottom: 8px;
        color: #334155;
    }

    .media-inline-upload .media-drop-zone {
        padding: 14px 10px;
        margin-bottom: 10px;
    }

    .media-inline-upload .media-upload-btn {
        width: 100%;
    }

    .lightbox-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 46px;
        height: 46px;
        border: none;
        border-radius: 999px;
        background: rgba(30, 58, 138, 0.8);
        color: #fff;
        font-size: 24px;
        cursor: pointer;
        z-index: 1002;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-btn:hover {
        background: rgba(59, 130, 246, 0.95);
    }

    .lightbox-prev {
        left: 24px;
    }

    .lightbox-next {
        right: 24px;
    }

    .lightbox-counter {
        position: absolute;
        bottom: 25px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.7);
        color: #fff;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 14px;
        z-index: 1002;
    }

    @media (max-width: 768px) {
        .lightbox-btn {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }

        .lightbox-prev {
            left: 12px;
        }

        .lightbox-next {
            right: 12px;
        }
    }
</style>

<div class="page-header" style="--gallery-header-bg: url('<?php echo htmlspecialchars($gallery_header_full_url); ?>');">
    <h1>School Gallery</h1>
    <p>Capturing Our School Moments</p>
    <p style="font-size:12px; opacity:0.85; margin-top:6px;">Build <?php echo htmlspecialchars($asset_version); ?></p>
    <?php if (!$show_admin_panel): ?>
        <p style="font-size:12px; margin-top:6px;">
            <a href="?admin=1" style="color:#dbeafe; text-decoration:underline;">Admin access</a>
        </p>
    <?php endif; ?>
</div>

<div class="page-content">
    <div class="gallery-info">
        <h2>Our School in Pictures</h2>
        <p>
            Explore our school through images of students, staff, events, and facilities. 
            These photos capture the spirit of learning and community that defines Nyakabingo Primary School.
        </p>
    </div>
    
    <?php if ($show_admin_panel): ?>
    <!-- Upload Section (Admin Only) -->
    <div class="upload-section">
        <h3>📸 Upload & Manage Media</h3>
        <?php if ($is_gallery_admin): ?>
            <div class="admin-pill">
                <form method="post" style="margin:0;">
                    <input type="hidden" name="gallery_auth_action" value="logout">
                    <button type="submit" class="upload-btn" style="padding:6px 12px; font-size:12px;">Logout</button>
                </form>
            </div>

            <form id="image-upload-form" class="upload-form" style="margin-top: 14px;">
                <div id="upload-status"></div>

                <div class="drag-drop-zone" id="drag-drop-zone">
                    <div class="icon">📷</div>
                    <p><strong>Drag & drop images here</strong></p>
                    <p style="margin-top: 10px; opacity: 0.7;">or click to select files</p>
                </div>

                <input type="file" id="image-input" name="image" accept="image/*">

                <div id="image-preview"></div>

                <button type="submit" id="upload-btn" class="upload-btn">Upload Image</button>
                <p style="font-size: 12px; color: #666; margin-top: 10px; text-align: center;">
                    ✓ Supported formats: JPG, PNG, GIF, WebP | ✓ Max size: 5MB
                </p>

                <div class="quick-upload-panel">
                    <h4>Quick Upload Targets</h4>
                    <p>Click any target below, choose a file, then click "Upload Image" to replace that specific photo slot.</p>
                    <div class="quick-upload-grid">
                        <button type="button" class="quick-upload-btn" data-target-filename="staff1.jpg">Staff Photo 1</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="staff2.jpg">Staff Photo 2</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="staff3.jpg">Staff Photo 3</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="staff4.jpg">Staff Photo 4</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="pupils1.jpg">Pupils Photo 1</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="pupils2.jpg">Pupils Photo 2</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="pupils3.jpg">Pupils Photo 3</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="pupils4.jpg">Pupils Photo 4</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="compound1.jpg">Compound Photo 1</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="compound2.jpg">Compound Photo 2</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="compound3.jpg">Compound Photo 3</button>
                        <button type="button" class="quick-upload-btn" data-target-filename="compound4.jpg">Compound Photo 4</button>
                    </div>
                </div>

                <div class="upload-diagnostics">
                    <strong>Media folder status:</strong><br>
                    Videos folder: <span class="<?php echo ($media_directory_status['videos']['writable'] ? 'diag-ok' : 'diag-bad'); ?>"><?php echo $media_directory_status['videos']['writable'] ? 'writable' : 'not writable'; ?></span><br>
                    Songs folder: <span class="<?php echo ($media_directory_status['songs']['writable'] ? 'diag-ok' : 'diag-bad'); ?>"><?php echo $media_directory_status['songs']['writable'] ? 'writable' : 'not writable'; ?></span><br>
                    Short videos folder: <span class="<?php echo ($media_directory_status['short_videos']['writable'] ? 'diag-ok' : 'diag-bad'); ?>"><?php echo $media_directory_status['short_videos']['writable'] ? 'writable' : 'not writable'; ?></span>
                </div>
            </form>
        <?php else: ?>
            <div class="auth-box show">
                <h4>Admin Login Required</h4>
                <?php if ($auth_error !== ''): ?>
                    <div class="auth-error"><?php echo htmlspecialchars($auth_error); ?></div>
                <?php endif; ?>
                <p style="margin-bottom:10px; color:#475569;">Sign in to access upload, edit, replace, and delete tools.</p>
                <form method="post" action="?admin=1" autocomplete="off">
                    <input type="hidden" name="gallery_auth_action" value="login">
                    <div class="auth-row">
                        <input id="admin-email-input" class="auth-input" type="email" name="admin_email" placeholder="Admin email" autocomplete="off" required>
                        <input class="auth-input" type="password" name="admin_password" placeholder="Admin password" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="upload-btn">Login as Admin</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Gallery Display -->
    <div style="margin-top: 50px; margin-bottom: 30px;">
        <h3 style="color: #1e3a8a; margin-bottom: 20px; text-align: center;">
            <?php 
            if ($images_count > 0) {
                echo "School Gallery (" . $images_count . " photos)";
            } else {
                echo "School Gallery";
            }
            ?>
        </h3>
        
        <?php if ($images_count === 0): ?>
            <div class="no-images">
                <p>📸 No photos yet</p>
                <p style="opacity: 0.7;">Be the first to upload a photo using the form above!</p>
            </div>
        <?php else: ?>
            <div class="gallery-grid" id="gallery-grid">
                <?php foreach ($gallery_images as $index => $image): ?>
                    <div class="gallery-item"<?php echo $index >= $initial_visible_count ? ' style="display:none;"' : ''; ?>>
                        <?php if ($is_gallery_admin): ?>
                            <div class="item-manage">
                                <button
                                    type="button"
                                    class="mini-btn manage-rename"
                                    data-media-type="images"
                                    data-filename="<?php echo htmlspecialchars($image['name']); ?>"
                                    title="Rename image"
                                >Edit</button>
                                <button
                                    type="button"
                                    class="mini-btn manage-replace"
                                    data-media-type="images"
                                    data-filename="<?php echo htmlspecialchars($image['name']); ?>"
                                    title="Replace image file"
                                >Replace</button>
                                <button
                                    type="button"
                                    class="mini-btn delete manage-delete"
                                    data-media-type="images"
                                    data-filename="<?php echo htmlspecialchars($image['name']); ?>"
                                    title="Delete image"
                                >Delete</button>
                                <input type="file" class="manage-replace-input" accept="image/*" style="display:none;">
                            </div>
                        <?php endif; ?>
                        <img
                            src="<?php echo htmlspecialchars($image['thumb_url']); ?>"
                            data-full-src="<?php echo htmlspecialchars($image['image_url']); ?>"
                            alt="Gallery photo <?php echo $index + 1; ?>"
                            loading="lazy"
                            onclick="openLightboxByIndex(<?php echo $index; ?>)"
                        >
                        <div class="gallery-item-label"><?php echo date('M d, Y', $image['uploaded_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($images_count > $initial_visible_count): ?>
                <div class="gallery-actions">
                    <button id="load-more-gallery" class="load-more-btn" type="button">
                        Load More Photos
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Gallery Categories Info -->
    <div style="margin-top: 50px;">
        <h3 style="color: #1e3a8a; margin-bottom: 30px; text-align: center; font-size: 28px;">Gallery Categories</h3>
        <div class="gallery-categories">
            <div class="category-section category-students">
                <div class="category-background"></div>
                <div class="category-overlay">
                    <h3>Students & Staff</h3>
                    <p>Photos of our students, teachers, and school leadership</p>
                </div>
            </div>
            
            <div class="category-section category-sports">
                <div class="category-background"></div>
                <div class="category-overlay">
                    <h3>Sports & Events</h3>
                    <p>Sports day activities and competitions</p>
                </div>
            </div>
            
            <div class="category-section category-celebrations">
                <div class="category-background"></div>
                <div class="category-overlay">
                    <h3>School Celebrations</h3>
                    <p>Prize giving, cultural events, and performances</p>
                </div>
            </div>
            
            <div class="category-section category-creative">
                <div class="category-background"></div>
                <div class="category-overlay">
                    <h3>Creative Activities</h3>
                    <p>Art exhibitions, drama, music, and projects</p>
                </div>
            </div>
        </div>
    </div>

    <div class="media-section">
        <h3 class="media-title">School Media Hub</h3>
        <p class="media-subtitle">Play videos, songs, and short videos uploaded to the school gallery.</p>

        <div class="media-grid">
            <div class="media-card">
                <h4>Videos</h4>
                <?php if ($is_gallery_admin): ?>
                    <div class="media-inline-upload">
                        <div class="media-login-state">Admin logged in: uploads enabled.</div>
                        <form class="media-upload-form" data-media-type="videos" data-max-size="41943040">
                            <div class="media-upload-status"></div>
                            <div class="media-drop-zone">
                                <p><strong>Upload a video directly here</strong></p>
                                <p style="font-size:12px; opacity:0.75; margin-top:4px;">Drag/drop or click to select</p>
                            </div>
                            <input type="file" class="media-file-input" accept="video/*">
                            <div class="media-selected-file"></div>
                            <button type="submit" class="media-upload-btn">Upload Video</button>
                        </form>
                    </div>
                <?php endif; ?>
                <?php if (empty($video_items)): ?>
                    <div class="media-empty">No videos uploaded yet.</div>
                    <?php if ($is_gallery_admin): ?>
                        <div class="media-links" style="margin-top: 10px;">
                            <button type="button" class="media-link media-empty-action" data-media-type="videos">Upload Video Now</button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="media-list">
                        <?php foreach ($video_items as $video): ?>
                            <div class="media-item">
                                <video controls preload="metadata">
                                    <source src="<?php echo htmlspecialchars($video['url']); ?>" type="<?php echo htmlspecialchars($video['mime']); ?>">
                                    Your browser does not support video playback.
                                </video>
                                <p class="media-name"><?php echo htmlspecialchars($video['name']); ?></p>
                                <div class="media-links">
                                    <button type="button" class="media-link media-play-btn">Play</button>
                                    <a class="media-link" href="<?php echo htmlspecialchars($video['url']); ?>" target="_blank" rel="noopener">Open</a>
                                    <a class="media-link" href="<?php echo htmlspecialchars($video['url']); ?>" download>Download</a>
                                </div>
                                <?php if ($is_gallery_admin): ?>
                                    <div class="media-manage">
                                        <button type="button" class="mini-btn manage-rename" data-media-type="videos" data-filename="<?php echo htmlspecialchars($video['name']); ?>">Edit</button>
                                        <button type="button" class="mini-btn manage-replace" data-media-type="videos" data-filename="<?php echo htmlspecialchars($video['name']); ?>">Replace</button>
                                        <button type="button" class="mini-btn delete manage-delete" data-media-type="videos" data-filename="<?php echo htmlspecialchars($video['name']); ?>">Delete</button>
                                        <input type="file" class="manage-replace-input" accept="video/*" style="display:none;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="media-card">
                <h4>Songs</h4>
                <?php if ($is_gallery_admin): ?>
                    <div class="media-inline-upload">
                        <div class="media-login-state">Admin logged in: uploads enabled.</div>
                        <form class="media-upload-form" data-media-type="songs" data-max-size="15728640">
                            <div class="media-upload-status"></div>
                            <div class="media-drop-zone">
                                <p><strong>Upload a song directly here</strong></p>
                                <p style="font-size:12px; opacity:0.75; margin-top:4px;">Drag/drop or click to select</p>
                            </div>
                            <input type="file" class="media-file-input" accept="audio/*">
                            <div class="media-selected-file"></div>
                            <button type="submit" class="media-upload-btn">Upload Song</button>
                        </form>
                    </div>
                <?php endif; ?>
                <?php if (empty($song_items)): ?>
                    <div class="media-empty">No songs uploaded yet.</div>
                    <?php if ($is_gallery_admin): ?>
                        <div class="media-links" style="margin-top: 10px;">
                            <button type="button" class="media-link media-empty-action" data-media-type="songs">Upload Song Now</button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="media-list">
                        <?php foreach ($song_items as $song): ?>
                            <div class="media-item">
                                <audio controls preload="metadata">
                                    <source src="<?php echo htmlspecialchars($song['url']); ?>" type="<?php echo htmlspecialchars($song['mime']); ?>">
                                    Your browser does not support audio playback.
                                </audio>
                                <p class="media-name"><?php echo htmlspecialchars($song['name']); ?></p>
                                <div class="media-links">
                                    <button type="button" class="media-link media-play-btn">Play</button>
                                    <a class="media-link" href="<?php echo htmlspecialchars($song['url']); ?>" target="_blank" rel="noopener">Open</a>
                                    <a class="media-link" href="<?php echo htmlspecialchars($song['url']); ?>" download>Download</a>
                                </div>
                                <?php if ($is_gallery_admin): ?>
                                    <div class="media-manage">
                                        <button type="button" class="mini-btn manage-rename" data-media-type="songs" data-filename="<?php echo htmlspecialchars($song['name']); ?>">Edit</button>
                                        <button type="button" class="mini-btn manage-replace" data-media-type="songs" data-filename="<?php echo htmlspecialchars($song['name']); ?>">Replace</button>
                                        <button type="button" class="mini-btn delete manage-delete" data-media-type="songs" data-filename="<?php echo htmlspecialchars($song['name']); ?>">Delete</button>
                                        <input type="file" class="manage-replace-input" accept="audio/*" style="display:none;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="media-card">
                <h4>Short Videos</h4>
                <?php if ($is_gallery_admin): ?>
                    <div class="media-inline-upload">
                        <div class="media-login-state">Admin logged in: uploads enabled.</div>
                        <form class="media-upload-form" data-media-type="short_videos" data-max-size="20971520">
                            <div class="media-upload-status"></div>
                            <div class="media-drop-zone">
                                <p><strong>Upload a short video directly here</strong></p>
                                <p style="font-size:12px; opacity:0.75; margin-top:4px;">Drag/drop or click to select</p>
                            </div>
                            <input type="file" class="media-file-input" accept="video/*">
                            <div class="media-selected-file"></div>
                            <button type="submit" class="media-upload-btn">Upload Short Video</button>
                        </form>
                    </div>
                <?php endif; ?>
                <?php if (empty($short_video_items)): ?>
                    <div class="media-empty">No short videos uploaded yet.</div>
                    <?php if ($is_gallery_admin): ?>
                        <div class="media-links" style="margin-top: 10px;">
                            <button type="button" class="media-link media-empty-action" data-media-type="short_videos">Upload Short Video Now</button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="media-list">
                        <?php foreach ($short_video_items as $short_video): ?>
                            <div class="media-item">
                                <video controls preload="metadata">
                                    <source src="<?php echo htmlspecialchars($short_video['url']); ?>" type="<?php echo htmlspecialchars($short_video['mime']); ?>">
                                    Your browser does not support video playback.
                                </video>
                                <p class="media-name"><?php echo htmlspecialchars($short_video['name']); ?></p>
                                <div class="media-links">
                                    <button type="button" class="media-link media-play-btn">Play</button>
                                    <a class="media-link" href="<?php echo htmlspecialchars($short_video['url']); ?>" target="_blank" rel="noopener">Open</a>
                                    <a class="media-link" href="<?php echo htmlspecialchars($short_video['url']); ?>" download>Download</a>
                                </div>
                                <?php if ($is_gallery_admin): ?>
                                    <div class="media-manage">
                                        <button type="button" class="mini-btn manage-rename" data-media-type="short_videos" data-filename="<?php echo htmlspecialchars($short_video['name']); ?>">Edit</button>
                                        <button type="button" class="mini-btn manage-replace" data-media-type="short_videos" data-filename="<?php echo htmlspecialchars($short_video['name']); ?>">Replace</button>
                                        <button type="button" class="mini-btn delete manage-delete" data-media-type="short_videos" data-filename="<?php echo htmlspecialchars($short_video['name']); ?>">Delete</button>
                                        <input type="file" class="manage-replace-input" accept="video/*" style="display:none;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="note-box">
        <p>
            💡 Upload photos, videos, songs, and short videos using the forms above, then Play and manage them with Edit, Replace, and Delete.
        </p>
    </div>
</div>

<!-- Lightbox for viewing full images -->
<div id="lightbox" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1000; justify-content: center; align-items: center;" onclick="closeLightbox(event)">
    <button type="button" class="lightbox-btn lightbox-prev" onclick="showPreviousImage(event)">‹</button>
    <img id="lightbox-image" style="max-width: 90%; max-height: 90%; border-radius: 10px;" alt="Expanded gallery image">
    <button type="button" class="lightbox-btn lightbox-next" onclick="showNextImage(event)">›</button>
    <div id="lightbox-counter" class="lightbox-counter"></div>
    <span style="position: absolute; top: 20px; right: 40px; color: white; font-size: 40px; cursor: pointer;" onclick="closeLightbox(event)">✕</span>
</div>

<script>
window.GALLERY_IS_ADMIN = <?php echo $is_gallery_admin ? 'true' : 'false'; ?>;
window.GALLERY_MEDIA_UPLOAD_ENDPOINT = 'upload_media.php';
window.GALLERY_MANAGE_ENDPOINT = 'manage_uploads.php';
</script>
<script src="/NYAKABINGO_PRIMARY/js/upload.js?v=<?php echo rawurlencode($asset_version); ?>"></script>
<script src="/NYAKABINGO_PRIMARY/js/media_upload.js?v=<?php echo rawurlencode($asset_version); ?>"></script>
<script src="/NYAKABINGO_PRIMARY/js/manage_uploads.js?v=<?php echo rawurlencode($asset_version); ?>"></script>
<script>
const galleryImages = <?php
    echo json_encode(
        array_map(static function ($image) {
            return $image['image_url'];
        },
        $gallery_images),
        JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    );
?>;

let currentLightboxIndex = -1;

function setLightboxImage(index) {
    if (!galleryImages.length) {
        return;
    }

    if (index < 0) {
        index = galleryImages.length - 1;
    }
    if (index >= galleryImages.length) {
        index = 0;
    }

    currentLightboxIndex = index;

    const lightbox = document.getElementById('lightbox');
    const image = document.getElementById('lightbox-image');
    const counter = document.getElementById('lightbox-counter');

    image.src = galleryImages[currentLightboxIndex];
    if (counter) {
        counter.textContent = (currentLightboxIndex + 1) + ' / ' + galleryImages.length;
    }
    lightbox.style.display = 'flex';
}

function openLightboxByIndex(index) {
    setLightboxImage(index);
}

function openLightbox(src) {
    const index = galleryImages.indexOf(src);
    if (index >= 0) {
        setLightboxImage(index);
        return;
    }

    const lightbox = document.getElementById('lightbox');
    const image = document.getElementById('lightbox-image');
    const counter = document.getElementById('lightbox-counter');
    image.src = src;
    if (counter) {
        counter.textContent = '';
    }
    lightbox.style.display = 'flex';
}

function showPreviousImage(event) {
    if (event) {
        event.stopPropagation();
    }
    setLightboxImage(currentLightboxIndex - 1);
}

function showNextImage(event) {
    if (event) {
        event.stopPropagation();
    }
    setLightboxImage(currentLightboxIndex + 1);
}

function closeLightbox(event) {
    if (event) {
        event.stopPropagation();
    }
    document.getElementById('lightbox').style.display = 'none';
}

// Click on drag-drop zone to trigger file input
document.addEventListener('DOMContentLoaded', function() {
    const dragDropZone = document.getElementById('drag-drop-zone');
    const fileInput = document.getElementById('image-input');
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const loadMoreBtn = document.getElementById('load-more-gallery');

    if (dragDropZone && fileInput) {
        dragDropZone.addEventListener('click', () => fileInput.click());
    }

    if (lightboxImage) {
        lightboxImage.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }

    document.addEventListener('keydown', function(event) {
        if (!lightbox || lightbox.style.display !== 'flex') {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
        } else if (event.key === 'ArrowLeft') {
            showPreviousImage();
        } else if (event.key === 'ArrowRight') {
            showNextImage();
        }
    });

    if (loadMoreBtn) {
        const hiddenItems = Array.from(document.querySelectorAll('#gallery-grid .gallery-item[style*="display:none"]'));
        let revealIndex = 0;
        const revealBatchSize = 8;

        loadMoreBtn.addEventListener('click', function() {
            const nextItems = hiddenItems.slice(revealIndex, revealIndex + revealBatchSize);
            nextItems.forEach(function(item) {
                item.style.display = '';
            });
            revealIndex += nextItems.length;

            if (revealIndex >= hiddenItems.length) {
                loadMoreBtn.disabled = true;
                loadMoreBtn.textContent = 'All Photos Loaded';
            }
        });
    }

    const mediaPlayButtons = Array.from(document.querySelectorAll('.media-play-btn'));
    mediaPlayButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const mediaItem = button.closest('.media-item');
            if (!mediaItem) {
                return;
            }

            const media = mediaItem.querySelector('audio, video');
            if (!media) {
                return;
            }

            if (media.paused) {
                media.play().catch(function() {});
                button.textContent = 'Pause';
            } else {
                media.pause();
                button.textContent = 'Play';
            }
        });
    });

    // Clear login form inputs on page load for security
    const adminEmailInput = document.querySelector('input[name="admin_email"]');
    const adminPasswordInput = document.querySelector('input[name="admin_password"]');
    if (adminEmailInput) adminEmailInput.value = '';
    if (adminPasswordInput) adminPasswordInput.value = '';
});
</script>

<?php include '../includes/footer.php'; ?>
