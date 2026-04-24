<?php
/**
 * Media Upload Handler
 * Supports uploads for videos, songs, and short videos.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/gallery_admin_auth.php';
gallery_admin_bootstrap();

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=UTF-8');

function respond_json(bool $success, string $message, string $file = '', string $mediaType = ''): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'file' => $file,
        'mediaType' => $mediaType,
    ]);
    exit;
}

function upload_error_message(int $code): string
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Upload failed: file exceeds server limit (upload_max_filesize=' . ini_get('upload_max_filesize') . ', post_max_size=' . ini_get('post_max_size') . ').';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Upload failed: file exceeds form upload limit.';
        case UPLOAD_ERR_PARTIAL:
            return 'Upload failed: file was only partially uploaded.';
        case UPLOAD_ERR_NO_FILE:
            return 'Upload failed: no file was uploaded.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Upload failed: missing temporary folder on server.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Upload failed: server could not write file to disk.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload failed: blocked by a PHP extension.';
        default:
            return 'Upload failed with error code: ' . $code;
    }
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

function ensure_directory_ready(string $dir): bool
{
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }
    }

    if (!is_writable($dir)) {
        @chmod($dir, 0775);
    }

    return is_writable($dir);
}

try {
    if (!gallery_is_admin()) {
        respond_json(false, 'Unauthorized. Admin login required.');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        respond_json(false, 'Invalid request method. Use POST.');
    }

    $mediaType = (string) ($_POST['media_type'] ?? '');

    $configs = [
        'videos' => [
            'dir' => dirname(__DIR__) . '/media/videos/',
            'fallbackDir' => dirname(__DIR__) . '/images/uploads/',
            'publicType' => 'video',
            'extensions' => ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv', 'mpg', 'mpeg', '3gp'],
            'maxSize' => 40 * 1024 * 1024,
        ],
        'songs' => [
            'dir' => dirname(__DIR__) . '/media/songs/',
            'fallbackDir' => dirname(__DIR__) . '/images/uploads/',
            'publicType' => 'audio',
            'extensions' => ['mp3', 'mpga', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'opus', 'amr', 'wma'],
            'maxSize' => 15 * 1024 * 1024,
        ],
        'short_videos' => [
            'dir' => dirname(__DIR__) . '/media/short_videos/',
            'fallbackDir' => dirname(__DIR__) . '/images/uploads/',
            'publicType' => 'video',
            'extensions' => ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv', 'mpg', 'mpeg', '3gp'],
            'maxSize' => 20 * 1024 * 1024,
        ],
    ];

    if (!isset($configs[$mediaType])) {
        respond_json(false, 'Unsupported media type.');
    }

    if (!isset($_FILES['media']) || !is_array($_FILES['media'])) {
        respond_json(false, 'No media file provided. Ensure form field name is media and upload is enabled.', '', $mediaType);
    }

    $file = $_FILES['media'];
    $config = $configs[$mediaType];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        respond_json(false, upload_error_message((int) ($file['error'] ?? 0)), '', $mediaType);
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        respond_json(false, 'Invalid uploaded file.', '', $mediaType);
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > $config['maxSize']) {
        respond_json(false, 'File is too large. Limits: Video 40MB, Song 15MB, Short video 20MB.', '', $mediaType);
    }

    $originalName = (string) ($file['name'] ?? 'media.bin');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, $config['extensions'], true)) {
        respond_json(false, 'Unsupported file extension for this section.', '', $mediaType);
    }

    $mime = detect_mime_type($tmpName, (string) ($file['type'] ?? ''));
    $typePrefix = $config['publicType'] . '/';
    if (strpos($mime, $typePrefix) !== 0 && $mime !== 'application/octet-stream' && $mime !== 'video/3gpp' && $mime !== 'audio/x-m4a') {
        respond_json(false, 'Invalid file type detected (' . $mime . ').', '', $mediaType);
    }

    $targetDir = $config['dir'];
    if (!ensure_directory_ready($targetDir)) {
        $fallbackDir = (string) ($config['fallbackDir'] ?? '');
        if ($fallbackDir === '' || !ensure_directory_ready($fallbackDir)) {
            respond_json(false, 'Failed to prepare target folders (not writable): ' . $targetDir, '', $mediaType);
        }
        $targetDir = $fallbackDir;
    }

    $newName = strtoupper($mediaType) . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetPath = $targetDir . $newName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        $copied = @copy($tmpName, $targetPath);
        if (!$copied) {
            respond_json(false, 'Failed to save uploaded media file. Please check folder permissions.', '', $mediaType);
        }
    }

    respond_json(true, 'Media uploaded successfully!', $newName, $mediaType);
} catch (Throwable $e) {
    respond_json(false, 'Server failed to process upload. Please try again.');
}
