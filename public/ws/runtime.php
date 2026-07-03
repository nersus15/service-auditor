<?php

class Runtime {
    public function __construct()
    {
        header("Content-type: application/json");
        require_once __DIR__ . '/../../app/functions.php';
    }
    function index(){
        $url = $_GET['url'] ?? null;

        $config = serviceAuditorConfig();
        $logFile = serviceAuditorRuntimeLogFile($config, $url);
        $lines = [];

        if (is_file($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }

        echo json_encode(['data' => $lines]);
    }
}