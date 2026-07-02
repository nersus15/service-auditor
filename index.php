<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = '/' . trim($uri, '/');
$publicDir = __DIR__ . '/public';

if ($uri === '/' || $uri === '/index.php') {
    require $publicDir . '/index.php';
    exit;
}

if ($uri === '/settings' || $uri === '/settings.php') {
    require $publicDir . '/settings.php';
    exit;
}
if (strpos($uri, '/assets/') === 0 && is_file($publicDir .  $uri)) {
    $filePath = $publicDir . $uri;
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
    ];
    // echo $mimeTypes[$extension];die;
    header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
    readfile($filePath);
    exit;
}

if (is_file($publicDir . $uri)) {
    require $publicDir . $uri;
    exit;
}

require $publicDir . '/index.php';
