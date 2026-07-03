<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$settingsFile = $baseDir . '/app/settings.json';
$settings = [];

if (is_file($settingsFile)) {
    $raw = file_get_contents($settingsFile);
    if ($raw !== false) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $settings = $decoded;
        }
    }
}

return [
    'app_root' => $baseDir,
    'public_root' => $baseDir . '/public',
    'log_root' => $baseDir . '/logs',
    'settings_file' => $settingsFile,
    'check_urls' => $settings['check_urls'],
    'check_url' => getenv('SERVICE_AUDITOR_URL') ?: ($settings['check_url'] ?? ($settings['check_urls'][0] ?? 'https://example.com/health')),
    'check_interval_minutes' => (int) (getenv('SERVICE_AUDITOR_INTERVAL_MINUTES') ?: ($settings['check_interval_minutes'] ?? 5)),
    'check_schedule' => $settings['check_schedule'] ?? '*/5 * * * *',
    'timeout_seconds' => (int) ($settings['timeout_seconds'] ?? 10),
    'log_extension' => 'log',
];
