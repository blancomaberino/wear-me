<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// If the file exists as a static file, serve it with proper streaming
// instead of returning false (which lets PHP's built-in server handle it
// and can cause ERR_CONTENT_LENGTH_MISMATCH on large files).
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    $filePath = $publicPath.$uri;
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'ico' => 'image/x-icon',
    ];

    // For known static file types, stream them ourselves
    if (isset($mimeTypes[$extension])) {
        $fileSize = filesize($filePath);
        header('Content-Type: ' . $mimeTypes[$extension]);
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: public, max-age=86400');

        readfile($filePath);
        return true;
    }

    // For other file types (like .php), let the built-in server handle it
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');
$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
