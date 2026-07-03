<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/functions.php';

$config = serviceAuditorConfig();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Runtime Log - Service Auditor</title>
    <link rel="stylesheet" href="/assets/style.css">

    <script src="/assets/scripts/jquery.min.js"></script>
</head>
<body>
    <div class="page-shell">
        <header class="hero-card">
            <div>
                <p class="eyebrow">Monitoring Log</p>
                <h1>Runtime Activity</h1>
                <p class="hero-copy">Lihat riwayat apakah pengecekan dijalankan, di-skip, atau terjadi error.  <span class="badge ok">Auto Reload Setiap 1m</span></p>
            </div>
        </header>

        <nav class="url-navbar">
            <div class="url-nav-container">
                <label class="url-label">Pilih URL:</label>
                <select id="urlSelector" class="url-select">
                    <?php 
                    $urls = $config['check_urls'] ?? [$config['check_url']];
                    foreach ($urls as $u): 
                    ?>
                        <option value="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </nav>

        <main class="dashboard-grid2">
            <section class="card wide-card" >
                <div class="card-header">
                    <h2>Recent Runtime Log</h2>
                    <a class="chip" href="/">Kembali ke Dashboard</a>
                </div>
                <div style="max-height: 600px; overflow-y: scroll;">
                    <pre style="white-space: pre-wrap; word-break: break-word;"></pre>
                </div>
            </section>
        </main>
    </div>


    <script src="/assets/scripts/runtime.js"></script>
</body>
</html>
