<?php

declare(strict_types=1);

require_once __DIR__ . '/app/functions.php';

$config = serviceAuditorConfig();

if (!serviceAuditorShouldRun($config)) {
    echo 'Skipped: interval not reached yet.' . PHP_EOL;
    exit(0);
}

$result = serviceAuditorRunCheck($config);
serviceAuditorMarkRun($config);

echo 'Check completed for ' . $result['url'] . PHP_EOL;
echo 'Status: ' . ($result['success'] ? 'success' : 'error') . PHP_EOL;
echo 'Saved to: ' . $result['log_file'] . PHP_EOL;
