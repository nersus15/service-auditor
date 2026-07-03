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
    $folder = $config['log_root'] . '/' . serviceAuditorSafeFolderName($url) . "/results";

    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        throw new RuntimeException('Unable to create log directory: ' . $folder);
    }

    return $folder;
}

function serviceAuditorLogFilePath(string $url, array $config): string
{
    $directory = serviceAuditorLogDirectory($url, $config);
    $timestamp = date('Y-m-d');

    return $directory . '/' . $timestamp . '.' . $config['log_extension'];
}

function serviceAuditorRunCheck(array $config, string $url): array
{
    $startedAt = microtime(true);
    $ch = curl_init($url);

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
        'url' => $url,
        'success' => $success,
        'status_code' => $httpCode,
        'response_time_ms' => $responseTimeMs,
        'error' => $error !== '' ? $error : null,
        'body_excerpt' => $bodyExcerpt,
    ];

    $logFile = serviceAuditorLogFilePath($url, $config);
    serviceAuditorAppendDailyLog($logFile, $result);
    $result['log_file'] = $logFile;

    return $result;
}

function serviceAuditorAppendDailyLog(string $logFile, array $entry): void
{
    $records = [];

    if (is_file($logFile)) {
        $contents = file_get_contents($logFile);
        if ($contents !== false) {
            $decoded = json_decode($contents, true);
            if (is_array($decoded)) {
                $records = $decoded;
            }
        }
    }

    $records[] = $entry;
    file_put_contents($logFile, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function serviceAuditorSaveSettings(array $config, array $data): array
{
    $settingsPath = $config['settings_file'] ?? dirname(__DIR__) . '/app/settings.json';
    
    // Parse multiple URLs from textarea (handle \r\n, \n, \r)
    $urlsInput = trim((string) ($data['check_urls'] ?? ''));
    $urls = array_filter(array_map('trim', preg_split('/\r?\n/', $urlsInput)));
    $urls = array_values($urls); // Re-index array
    
    if (empty($urls)) {
        throw new RuntimeException('Minimal satu URL harus diisi');
    }
    
    $payload = [
        'check_urls' => $urls,
        'check_url' => $urls[0], // Backward compatibility
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

function serviceAuditorShouldRun(array $config, string $url): bool
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

    if(!isset($decoded[$url])) return true;

    $timestamp = $decoded[$url]['timestamp'] ?? null;

    if (!is_string($timestamp)) {
        return true;
    }

    $lastTime = strtotime($timestamp);
    if ($lastTime === false) {
        return true;
    }

    return (time() - $lastTime) >= $intervalSeconds;
}

function serviceAuditorMarkRun(array $config, string $url): void
{
    $lastRunFile = serviceAuditorLastRunFile($config);

    if(!is_file($lastRunFile)){
        @file_put_contents($lastRunFile, json_encode([], JSON_UNESCAPED_SLASHES));
    }

    $raw = file_get_contents($lastRunFile);
    $payload = [];
    if ($raw === false) {
        $payload["url"] = ['timestamp' => gmdate('c')];
    }
    $decoded = json_decode($raw, true);
    $payload = $decoded;
    $payload[$url] = ['timestamp' => gmdate('c')];
    @file_put_contents($lastRunFile, json_encode($payload, JSON_UNESCAPED_SLASHES));
}

function serviceAuditorRuntimeLogFile(array $config, string $url): string
{
    $folder = $config['log_root'] . '/' . serviceAuditorSafeFolderName($url);

    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        throw new RuntimeException('Unable to create log directory: ' . $folder);
    }

    return $folder . '/runtime.log';
}

function serviceAuditorWriteRuntimeLog(array $config, string $url, string $status, string $message): void
{
    $logFile = serviceAuditorRuntimeLogFile($config, $url);
    $line = '[' . gmdate('c') . '] ' . $status . ' - ' . $message . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND);
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

function serviceAuditorLoadResults(array $config, ?string $url = null): array
{
    if (!is_dir($config['log_root'])) {
        return [];
    }

    // If URL is specified, only load logs for that URL
    if ($url !== null && $url !== '') {
        $logDir = serviceAuditorLogDirectory($url, $config);
        if (!is_dir($logDir)) {
            return [];
        }
        
        $results = [];
        $files = glob($logDir . '/*.' . $config['log_extension']);
        
        if (is_array($files)) {
            foreach ($files as $file) {
                $contents = file_get_contents($file);
                if ($contents === false) continue;
                
                $decoded = json_decode(trim($contents), true);
                if (!is_array($decoded)) continue;
                
                foreach ($decoded as $entry) {
                    if (is_array($entry)) {
                        $entry['log_file'] = $file;
                        $results[] = $entry;
                    }
                }
            }
        }
    } else {
        // Load all results from all URLs
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
            if (!is_array($decoded)) {
                continue;
            }
            foreach ($decoded as $entry) {
                if (is_array($entry)) {
                    $entry['log_file'] = $fileInfo->getPathname();
                    $results[] = $entry;
                }
            }
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
