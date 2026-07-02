<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$config = serviceAuditorConfig();
$intervalSeconds = max(1, (int) ($config['check_interval_minutes'] ?? 5) * 60);
$loopDelay = 1;

while (true) {
    $now = time();
    $lastRun = 0;
    $lastRunFile = $config['app_root'] . '/app/.last_run.json';

    if (is_file($lastRunFile)) {
        $raw = @file_get_contents($lastRunFile);
        if ($raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded['timestamp'])) {
                $lastRun = strtotime((string) $decoded['timestamp']);
            }
        }
    }

    if ($lastRun === 0 || ($now - $lastRun) >= $intervalSeconds) {
        $url = 'http://127.0.0.1:' . ($_SERVER['PORT'] ?? '8080') . '/run-check.php';
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        @file_get_contents($url, false, $context);
    }

    sleep($loopDelay);
}
