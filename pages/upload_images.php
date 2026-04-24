<?php
/**
 * Image Upload Handler for Gallery
 * Always returns JSON (never HTML warnings/notices)
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/gallery_admin_auth.php';
gallery_admin_bootstrap();

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=UTF-8');

/**
 * Return JSON and stop script execution.
 */
function respond_json(bool $success, string $message, string $file = ''): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'file' => $file,
    ]);
    exit;
}

/**
 * Detect a reliable MIME type for uploaded file.
 */
function detect_mime_type(string $tmpPath, string $fallback): string
{
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
            if (is_string($detected) && $detected !== '') {
                return $detected;
            }
        }
    }

    return $fallback;
}

/**
 * Validate and sanitize an optional target filename for quick upload slots.
 */
function validate_target_filename(string $filename): string
{
    $filename = strtolower(trim($filename));
    if ($filename === '') {
        return '';
    }

    if (!preg_match('/^[a-z0-9._-]+$/', $filename)) {
        return '';
    }

    $allowed = [
        'staff1.jpg', 'staff2.jpg', 'staff3.jpg', 'staff4.jpg',
        'pupils1.jpg', 'pupils2.jpg', 'pupils3.jpg', 'pupils4.jpg',
        'compound1.jpg', 'compound2.jpg', 'compound3.jpg', 'compound4.jpg',
    ];

    return in_array($filename, $allowed, true) ? $filename : '';
}

try {
    if (!gallery_is_admin()) {
        respond_json(false, 'Unauthorized. Admin login required.');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        respond_json(false, 'Invalid request method. Use POST.');
    }

    if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
        respond_json(false, 'No file provided!');
    }

    $file = $_FILES['image'];
    $upload_dir = dirname(__DIR__) . '/images/uploads/';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        respond_json(false, 'Failed to prepare upload directory.');
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        respond_json(false, 'Upload error: ' . (string) ($file['error'] ?? 'unknown'));
    }

    $tmp_name = $file['tmp_name'] ?? '';
    if (!is_string($tmp_name) || $tmp_name === '' || !is_uploaded_file($tmp_name)) {
        respond_json(false, 'Invalid uploaded file.');
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > $max_size) {
        respond_json(false, 'File size exceeds 5MB limit!');
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $detected_mime = detect_mime_type($tmp_name, (string) ($file['type'] ?? ''));
    if (!in_array($detected_mime, $allowed_types, true)) {
        respond_json(false, 'Only JPG, PNG, GIF, and WebP images are allowed!');
    }

    $original_name = (string) ($file['name'] ?? 'image.jpg');
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    if ($file_ext === '') {
        $mime_to_ext = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        $file_ext = $mime_to_ext[$detected_mime] ?? 'jpg';
    }

    $requested_target = validate_target_filename((string) ($_POST['target_filename'] ?? ''));
    $new_filename = $requested_target !== ''
        ? $requested_target
        : ('IMG_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext);

    $thumb_to_remove = $upload_dir . 'thumb_' . $new_filename;
    if (is_file($thumb_to_remove)) {
        @unlink($thumb_to_remove);
    }

    $upload_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($tmp_name, $upload_path)) {
        respond_json(false, 'Failed to move uploaded file!');
    }

    // Thumbnail generation failure should not fail upload.
    $thumbnail_path = $upload_dir . 'thumb_' . $new_filename;
    @create_thumbnail($upload_path, $thumbnail_path, 300, 300);

    respond_json(true, 'Image uploaded successfully!', $new_filename);
} catch (Throwable $e) {
    respond_json(false, 'Upload failed on server. Please try again.');
}

/**
 * Create thumbnail from image
 */
function create_thumbnail($source, $dest, $width, $height) {
    if (!function_exists('imagecreatetruecolor') || !is_file($source)) {
        return false;
    }

    if (function_exists('exif_imagetype')) {
        $image_type = @exif_imagetype($source);
    } else {
        $image_info = @getimagesize($source);
        $image_type = $image_info[2] ?? false;
    }
    
    switch($image_type) {
        case IMAGETYPE_JPEG:
            if (!function_exists('imagecreatefromjpeg')) return false;
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            if (!function_exists('imagecreatefrompng')) return false;
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            if (!function_exists('imagecreatefromgif')) return false;
            $image = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            if (!function_exists('imagecreatefromwebp')) return false;
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    if (!$image) return false;
    
    $src_width = imagesx($image);
    $src_height = imagesy($image);
    
    // Calculate dimensions to maintain aspect ratio
    $ratio = min($width / $src_width, $height / $src_height);
    $new_width = floor($src_width * $ratio);
    $new_height = floor($src_height * $ratio);
    
    $thumbnail = imagecreatetruecolor($width, $height);
    if (!$thumbnail) {
        imagedestroy($image);
        return false;
    }
    $bg_color = imagecolorallocate($thumbnail, 255, 255, 255);
    imagefill($thumbnail, 0, 0, $bg_color);
    
    $x = floor(($width - $new_width) / 2);
    $y = floor(($height - $new_height) / 2);
    
    imagecopyresampled($thumbnail, $image, $x, $y, 0, 0, $new_width, $new_height, $src_width, $src_height);
    
    // Save thumbnail
    switch($image_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail, $dest, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail, $dest);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbnail, $dest);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($thumbnail, $dest);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($thumbnail);
    return true;
}
?>
