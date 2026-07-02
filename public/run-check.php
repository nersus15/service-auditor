<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/functions.php';

$config = serviceAuditorConfig();

try {
    if (!serviceAuditorShouldRun($config)) {
        serviceAuditorWriteRuntimeLog($config, 'SKIPPED', 'Interval not reached yet.');
        echo json_encode(['status' => 'skipped', 'message' => 'Interval not reached yet.']);
        exit;
    }

    $result = serviceAuditorRunCheck($config);
    serviceAuditorMarkRun($config);
    serviceAuditorWriteRuntimeLog($config, 'RUN', 'Checked ' . $result['url'] . ' -> ' . ($result['success'] ? 'success' : 'error'));
    echo json_encode(['status' => 'ok', 'result' => $result]);
} catch (Throwable $e) {
    serviceAuditorWriteRuntimeLog($config, 'ERROR', $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
