<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/functions.php';

$config = serviceAuditorConfig();

foreach($config['check_urls'] as $url){
    run($url);
}

function run(string $url){
    global $config;
    try {
        if (!serviceAuditorShouldRun($config, $url)) {
            serviceAuditorWriteRuntimeLog($config, $url, 'SKIPPED', 'Interval not reached yet.');
            echo json_encode(['status' => 'skipped', 'message' => 'Interval not reached yet.']);
        }else{
            $result = serviceAuditorRunCheck($config, $url);
            serviceAuditorMarkRun($config, $url);
            serviceAuditorWriteRuntimeLog($config, $url, 'RUN', 'Checked ' . $result['url'] . ' -> ' . ($result['success'] ? 'success' : 'error'));
            echo json_encode(['status' => 'ok', 'result' => $result]);
        }
    
    } catch (Throwable $e) {
        serviceAuditorWriteRuntimeLog($config, $url, 'ERROR', $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

}