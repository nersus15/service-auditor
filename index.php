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
// var_dump(strpos($uri, '/assets/') === 0, $publicDir .  $uri, is_file($publicDir .  $uri));die;
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
    header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
    readfile($filePath);
    exit;
}


// Handle WS
if (strpos($uri, '/ws/') === 0){
    $uri2 = ltrim($uri, '/');
    $arr = explode("/", $uri2);
    $path = join("/", ["/", $arr[0], $arr[1]]);

    if (!is_file($publicDir . $path . ".php")){
        echo json_encode([
            "message" => "Controller ". $path ." Not Found",
            "code" => 404
        ]);

        exit;
    }

    require $publicDir . $path . ".php";
    $arr = explode("/", $uri2);
    $clsName = $arr[1];
    $clsName = ucfirst($clsName);
    $cls = new $clsName();

    $method = count($arr) == 2 ? "index" : $arr[2];
    
    if(count($arr) == 2){
        unset($arr[0], $arr[1]);
    }else{
        unset($arr[0], $arr[1], $arr[2]);
    }

    call_user_func_array([$cls, $method], array_values($arr));
    exit;

}

if (is_file($publicDir . $uri)) {
    require $publicDir . $uri;
    exit;
}

require $publicDir . '/index.php';
