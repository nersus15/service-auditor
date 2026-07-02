<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/functions.php';

$config = serviceAuditorConfig();

try {
    if (!serviceAuditorShouldRun($config)) {
        echo json_encode(['status' => 'skipped', 'message' => 'Interval not reached yet.']);
        exit;
    }

    $result = serviceAuditorRunCheck($config);
    serviceAuditorMarkRun($config);
    echo json_encode(['status' => 'ok', 'result' => $result]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
