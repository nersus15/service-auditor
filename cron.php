<?php

declare(strict_types=1);

require_once __DIR__ . '/app/functions.php';

$config = serviceAuditorConfig();

if (!serviceAuditorShouldRun($config)) {
    serviceAuditorWriteRuntimeLog($config, 'SKIPPED', 'Interval not reached yet.');
    echo 'Skipped: interval not reached yet.' . PHP_EOL;
    exit(0);
}

try {
    $result = serviceAuditorRunCheck($config);
    serviceAuditorMarkRun($config);
    serviceAuditorWriteRuntimeLog($config, 'RUN', 'Checked ' . $result['url'] . ' -> ' . ($result['success'] ? 'success' : 'error'));
    echo 'Check completed for ' . $result['url'] . PHP_EOL;
    echo 'Status: ' . ($result['success'] ? 'success' : 'error') . PHP_EOL;
    echo 'Saved to: ' . $result['log_file'] . PHP_EOL;
} catch (Throwable $e) {
    serviceAuditorWriteRuntimeLog($config, 'ERROR', $e->getMessage());
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
