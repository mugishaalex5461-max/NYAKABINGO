<?php
/**
 * Manage uploaded files (images/videos/songs/short videos)
 * Actions: delete, rename, replace
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/gallery_admin_auth.php';
gallery_admin_bootstrap();

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=UTF-8');

function respond_json(bool $success, string $message, string $filename = '', string $mediaType = ''): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'filename' => $filename,
        'mediaType' => $mediaType,
    ]);
    exit;
}

function sanitize_filename(string $name): string
{
    $name = basename($name);
    return preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?? '';
}

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

function create_thumbnail($source, $dest, $width, $height): bool
{
    if (!function_exists('imagecreatetruecolor') || !is_file($source)) {
        return false;
    }

    if (function_exists('exif_imagetype')) {
        $image_type = @exif_imagetype($source);
    } else {
        $image_info = @getimagesize($source);
        $image_type = $image_info[2] ?? false;
    }

    switch ($image_type) {
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
    if ($src_width <= 0 || $src_height <= 0) {
        imagedestroy($image);
        return false;
    }

    $ratio = min($width / $src_width, $height / $src_height);
    $new_width = (int) floor($src_width * $ratio);
    $new_height = (int) floor($src_height * $ratio);

    $thumbnail = imagecreatetruecolor($width, $height);
    if (!$thumbnail) {
        imagedestroy($image);
        return false;
    }

    $bg = imagecolorallocate($thumbnail, 255, 255, 255);
    imagefill($thumbnail, 0, 0, $bg);

    $x = (int) floor(($width - $new_width) / 2);
    $y = (int) floor(($height - $new_height) / 2);
    imagecopyresampled($thumbnail, $image, $x, $y, 0, 0, $new_width, $new_height, $src_width, $src_height);

    $saved = false;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $saved = imagejpeg($thumbnail, $dest, 90);
            break;
        case IMAGETYPE_PNG:
            $saved = imagepng($thumbnail, $dest);
            break;
        case IMAGETYPE_GIF:
            $saved = imagegif($thumbnail, $dest);
            break;
        case IMAGETYPE_WEBP:
            $saved = imagewebp($thumbnail, $dest);
            break;
    }

    imagedestroy($image);
    imagedestroy($thumbnail);
    return (bool) $saved;
}

try {
    if (!gallery_is_admin()) {
        respond_json(false, 'Unauthorized. Admin login required.');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        respond_json(false, 'Invalid request method. Use POST.');
    }

    $mediaType = (string) ($_POST['media_type'] ?? '');
    $action = (string) ($_POST['action'] ?? '');
    $filename = sanitize_filename((string) ($_POST['filename'] ?? ''));

    $configs = [
        'images' => [
            'dir' => dirname(__DIR__) . '/images/uploads/',
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'mimePrefix' => 'image/',
            'maxSize' => 5 * 1024 * 1024,
            'hasThumb' => true,
        ],
        'videos' => [
            'dir' => dirname(__DIR__) . '/media/videos/',
            'fallbackDir' => dirname(__DIR__) . '/images/uploads/',
            'extensions' => ['mp4', 'webm', 'ogg', 'mov', 'm4v'],
            'mimePrefix' => 'video/',
            'maxSize' => 100 * 1024 * 1024,
            'hasThumb' => false,
        ],
        'songs' => [
            'dir' => dirname(__DIR__) . '/media/songs/',
            'fallbackDir' => dirname(__DIR__) . '/images/uploads/',
            'extensions' => ['mp3', 'wav', 'ogg', 'm4a', 'aac'],
            'mimePrefix' => 'audio/',
            'maxSize' => 30 * 1024 * 1024,
            'hasThumb' => false,
        ],
        'short_videos' => [
            'dir' => dirname(__DIR__) . '/media/short_videos/',
            'fallbackDir' => dirname(__DIR__) . '/images/uploads/',
            'extensions' => ['mp4', 'webm', 'ogg', 'mov', 'm4v'],
            'mimePrefix' => 'video/',
            'maxSize' => 50 * 1024 * 1024,
            'hasThumb' => false,
        ],
    ];

    if (!isset($configs[$mediaType])) {
        respond_json(false, 'Unsupported media type.');
    }

    if ($filename === '') {
        respond_json(false, 'Missing filename.', '', $mediaType);
    }

    $config = $configs[$mediaType];
    $dir = $config['dir'];
    $filePath = $dir . $filename;

    if (!is_file($filePath) && !empty($config['fallbackDir'])) {
        $fallbackPath = $config['fallbackDir'] . $filename;
        if (is_file($fallbackPath)) {
            $dir = $config['fallbackDir'];
            $filePath = $fallbackPath;
        }
    }

    if (!is_file($filePath)) {
        respond_json(false, 'File not found.', $filename, $mediaType);
    }

    if ($action === 'delete') {
        if (!unlink($filePath)) {
            respond_json(false, 'Failed to delete file.', $filename, $mediaType);
        }

        if ($config['hasThumb']) {
            $thumbPath = $dir . 'thumb_' . $filename;
            if (is_file($thumbPath)) {
                @unlink($thumbPath);
            }
        }

        respond_json(true, 'File deleted successfully.', $filename, $mediaType);
    }

    if ($action === 'rename') {
        $newBase = trim((string) ($_POST['new_name'] ?? ''));
        $newBase = preg_replace('/[^A-Za-z0-9._-]/', '_', $newBase) ?? '';
        $newBase = trim($newBase, '.-_');
        if ($newBase === '') {
            respond_json(false, 'New name is invalid.', $filename, $mediaType);
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $newFilename = $newBase . '.' . $ext;
        $newFilename = sanitize_filename($newFilename);
        if ($newFilename === $filename) {
            respond_json(false, 'New name is same as current name.', $filename, $mediaType);
        }

        $newPath = $dir . $newFilename;
        if (is_file($newPath)) {
            respond_json(false, 'A file with this name already exists.', $filename, $mediaType);
        }

        if (!rename($filePath, $newPath)) {
            respond_json(false, 'Failed to rename file.', $filename, $mediaType);
        }

        if ($config['hasThumb']) {
            $oldThumb = $dir . 'thumb_' . $filename;
            $newThumb = $dir . 'thumb_' . $newFilename;
            if (is_file($oldThumb)) {
                @rename($oldThumb, $newThumb);
            }
        }

        respond_json(true, 'File renamed successfully.', $newFilename, $mediaType);
    }

    if ($action === 'replace') {
        if (!isset($_FILES['new_file']) || !is_array($_FILES['new_file'])) {
            respond_json(false, 'No replacement file provided.', $filename, $mediaType);
        }

        $newFile = $_FILES['new_file'];
        if (($newFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            respond_json(false, 'Upload error: ' . (string) ($newFile['error'] ?? 'unknown'), $filename, $mediaType);
        }

        $tmpName = (string) ($newFile['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            respond_json(false, 'Invalid replacement upload.', $filename, $mediaType);
        }

        $size = (int) ($newFile['size'] ?? 0);
        if ($size <= 0 || $size > $config['maxSize']) {
            respond_json(false, 'Replacement file is too large.', $filename, $mediaType);
        }

        $newExt = strtolower(pathinfo((string) ($newFile['name'] ?? ''), PATHINFO_EXTENSION));
        $currentExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($newExt, $config['extensions'], true) || $newExt !== $currentExt) {
            respond_json(false, 'Replacement file extension must match existing file type.', $filename, $mediaType);
        }

        $mime = detect_mime_type($tmpName, (string) ($newFile['type'] ?? ''));
        if (strpos($mime, $config['mimePrefix']) !== 0 && $mime !== 'application/octet-stream') {
            respond_json(false, 'Replacement file type is invalid.', $filename, $mediaType);
        }

        if (!move_uploaded_file($tmpName, $filePath)) {
            respond_json(false, 'Failed to replace file.', $filename, $mediaType);
        }

        if ($config['hasThumb']) {
            $thumbPath = $dir . 'thumb_' . $filename;
            @create_thumbnail($filePath, $thumbPath, 300, 300);
        }

        respond_json(true, 'File replaced successfully.', $filename, $mediaType);
    }

    respond_json(false, 'Unsupported action.', $filename, $mediaType);
} catch (Throwable $e) {
    respond_json(false, 'Server failed to process request.');
}
