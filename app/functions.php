<?php

declare(strict_types=1);

function serviceAuditorConfig(): array
{
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }

    return $config;
}

function serviceAuditorSafeFolderName(string $value): string
{
    $slug = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $value);
    $slug = trim((string) $slug, "-.");

    return $slug !== '' ? $slug : 'service';
}

function serviceAuditorLogDirectory(string $url, array $config): string
{
    $folder = $config['log_root'] . '/' . serviceAuditorSafeFolderName($url);

    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        throw new RuntimeException('Unable to create log directory: ' . $folder);
    }

    return $folder;
}

function serviceAuditorLogFilePath(string $url, array $config): string
{
    $directory = serviceAuditorLogDirectory($url, $config);
    $timestamp = date('Y-m-d-H-i-s');

    return $directory . '/' . $timestamp . '.' . $config['log_extension'];
}

function serviceAuditorRunCheck(array $config): array
{
    $startedAt = microtime(true);
    $ch = curl_init($config['check_url']);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $config['timeout_seconds'],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => false,
    ]);

    $body = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $responseTimeMs = (int) round((microtime(true) - $startedAt) * 1000);
    $success = $httpCode >= 200 && $httpCode < 400 && $error === '' && $body !== false;
    $bodyExcerpt = $body === false ? '' : substr(strip_tags((string) $body), 0, 180);

    $result = [
        'timestamp' => gmdate('c'),
        'url' => $config['check_url'],
        'success' => $success,
        'status_code' => $httpCode,
        'response_time_ms' => $responseTimeMs,
        'error' => $error !== '' ? $error : null,
        'body_excerpt' => $bodyExcerpt,
    ];

    $logFile = serviceAuditorLogFilePath($config['check_url'], $config);
    file_put_contents($logFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    $result['log_file'] = $logFile;

    return $result;
}

function serviceAuditorSaveSettings(array $config, array $data): array
{
    $settingsPath = $config['settings_file'] ?? dirname(__DIR__) . '/app/settings.json';
    $payload = [
        'check_url' => trim((string) ($data['check_url'] ?? '')),
        'check_interval_minutes' => max(1, (int) ($data['check_interval_minutes'] ?? 5)),
        'check_schedule' => trim((string) ($data['check_schedule'] ?? '*/5 * * * *')),
        'timeout_seconds' => max(3, (int) ($data['timeout_seconds'] ?? 10)),
    ];

    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    $written = @file_put_contents($settingsPath, $encoded);

    if ($written === false) {
        throw new RuntimeException('Unable to write settings file: ' . $settingsPath . ' (check permissions)');
    }

    return $payload;
}

function serviceAuditorLastRunFile(array $config): string
{
    return $config['app_root'] . '/app/.last_run.json';
}

function serviceAuditorShouldRun(array $config): bool
{
    $lastRunFile = serviceAuditorLastRunFile($config);
    $intervalSeconds = max(1, (int) ($config['check_interval_minutes'] ?? 5)) * 60;

    if (!is_file($lastRunFile)) {
        return true;
    }

    $raw = file_get_contents($lastRunFile);
    if ($raw === false) {
        return true;
    }

    $decoded = json_decode($raw, true);
    $timestamp = $decoded['timestamp'] ?? null;

    if (!is_string($timestamp)) {
        return true;
    }

    $lastTime = strtotime($timestamp);
    if ($lastTime === false) {
        return true;
    }

    return (time() - $lastTime) >= $intervalSeconds;
}

function serviceAuditorMarkRun(array $config): void
{
    $lastRunFile = serviceAuditorLastRunFile($config);
    $payload = ['timestamp' => gmdate('c')];
    @file_put_contents($lastRunFile, json_encode($payload, JSON_UNESCAPED_SLASHES));
}

function serviceAuditorLoadSettings(array $config): array
{
    $settingsPath = $config['settings_file'] ?? dirname(__DIR__) . '/app/settings.json';

    if (!is_file($settingsPath)) {
        return [];
    }

    $raw = file_get_contents($settingsPath);
    if ($raw === false) {
        return [];
    }

    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : [];
}

function serviceAuditorLoadResults(array $config): array
{
    if (!is_dir($config['log_root'])) {
        return [];
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($config['log_root'], FilesystemIterator::SKIP_DOTS)
    );

    $results = [];

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $extension = strtolower($fileInfo->getExtension());
        if ($extension !== $config['log_extension'] && $extension !== 'json') {
            continue;
        }

        $contents = file_get_contents($fileInfo->getPathname());
        if ($contents === false) {
            continue;
        }

        $decoded = json_decode(trim($contents), true);
        if (is_array($decoded)) {
            $decoded['log_file'] = $fileInfo->getPathname();
            $results[] = $decoded;
        }
    }

    usort($results, static function (array $left, array $right): int {
        return strcmp($right['timestamp'] ?? '', $left['timestamp'] ?? '');
    });

    return $results;
}

function serviceAuditorSummarize(array $results): array
{
    $total = count($results);
    $success = count(array_filter($results, static fn (array $result): bool => !empty($result['success'])));
    $error = $total - $success;
    $successRate = $total > 0 ? round(($success / $total) * 100) : 0;
    $latest = $results[0] ?? null;

    return [
        'total' => $total,
        'success' => $success,
        'error' => $error,
        'success_rate' => $successRate,
        'latest' => $latest,
    ];
}
