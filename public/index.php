<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/functions.php';

$config = serviceAuditorConfig();
$results = serviceAuditorLoadResults($config);
$summary = serviceAuditorSummarize($results);
$recentResults = array_slice($results, 0, 8);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Auditor</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <div class="page-shell">
        <header class="hero-card">
            <div>
                <p class="eyebrow">PHP Native Monitoring</p>
                <h1>Service Auditor Dashboard</h1>
                <p class="hero-copy">Pantau status backend secara sederhana, rapi, dan responsive melalui log curl yang tersimpan otomatis.</p>
            </div>
            <div class="hero-meta">
                <div>
                    <span class="meta-label">Target URL</span>
                    <strong><?= htmlspecialchars($config['check_url']) ?></strong>
                </div>
                <div>
                    <span class="meta-label">Interval</span>
                    <strong><?= (int) $config['check_interval_minutes'] ?> menit</strong>
                </div>
            </div>
        </header>

        <main class="dashboard-grid">
            <section class="card stat-card success">
                <div class="stat-title">Success</div>
                <div class="stat-value"><?= (int) $summary['success'] ?></div>
                <div class="stat-detail"><?= (int) $summary['success_rate'] ?>% dari total request</div>
            </section>
            <section class="card stat-card error">
                <div class="stat-title">Error</div>
                <div class="stat-value"><?= (int) $summary['error'] ?></div>
                <div class="stat-detail">Request yang gagal dipantau</div>
            </section>
            <section class="card stat-card total">
                <div class="stat-title">Total Checks</div>
                <div class="stat-value"><?= (int) $summary['total'] ?></div>
                <div class="stat-detail">Log tersimpan di folder logs</div>
            </section>

            <section class="card wide-card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                    <span class="chip">Last 8 checks</span>
                </div>

                <?php if ($recentResults === []): ?>
                    <p class="empty-state">Belum ada log yang dibuat. Jalankan cron terlebih dahulu untuk mengisi data.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Code</th>
                                    <th>Latency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentResults as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($item['timestamp'] ?? '-')) ?></td>
                                        <td>
                                            <span class="badge <?= !empty($item['success']) ? 'ok' : 'bad' ?>">
                                                <?= !empty($item['success']) ? 'Success' : 'Error' ?>
                                            </span>
                                        </td>
                                        <td><?= (int) ($item['status_code'] ?? 0) ?></td>
                                        <td><?= (int) ($item['response_time_ms'] ?? 0) ?> ms</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="steps">
                    <a class="chip" href="/settings.php">Buka Settings</a>
                    <a class="chip" href="/run-check.php">Run Check Now</a>
                    <a class="chip" href="/">Refresh Dashboard</a>
                </div>
                <div class="card-header">
                    <h2>How it works</h2>
                </div>
                <ul class="steps">
                    <li>Script cron memanggil URL target menggunakan cURL.</li>
                    <li>Hasil disimpan ke folder logs dengan struktur URL/filename.</li>
                    <li>Dashboard membaca seluruh log dan menampilkan ringkasan real-time.</li>
                </ul>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2>Cron Example</h2>
                </div>
                <code>*/5 * * * * php /path/to/service-auditor/cron.php >> /dev/null 2>&1</code>
            </section>
        </main>
    </div>
</body>
</html>
