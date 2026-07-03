<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/functions.php';

$config = serviceAuditorConfig();
$settings = serviceAuditorLoadSettings($config);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $settings = serviceAuditorSaveSettings($config, $_POST);
        $message = 'Pengaturan berhasil disimpan.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Service Auditor</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <div class="page-shell">
        <header class="hero-card">
            <div>
                <p class="eyebrow">Configuration</p>
                <h1>Pengaturan Service Auditor</h1>
                <p class="hero-copy">Ubah URL target, interval, dan jadwal cron dari halaman ini tanpa mengubah kode.</p>
            </div>
        </header>

        <main class="dashboard-grid">
            <section class="card wide-card">
                <div class="card-header">
                    <h2>Update Settings</h2>
                    <a class="chip" href="/">Kembali ke Dashboard</a>
                </div>

                <?php if ($message !== ''): ?>
                    <p class="empty-state" style="color:#4ade80;"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                    <p class="empty-state" style="color:#f87171;">Error: <?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form method="post" class="settings-form">
                    <label>
                        URL Target
                        <input type="url" name="check_url" value="<?= htmlspecialchars((string) ($settings['check_url'] ?? '')) ?>" required>
                    </label>
                    <label>
                        Interval (menit)
                        <input type="number" name="check_interval_minutes" min="1" value="<?= (int) ($settings['check_interval_minutes'] ?? 5) ?>" required>
                    </label>
                    <label>
                        Timeout (detik)
                        <input type="number" name="timeout_seconds" min="3" value="<?= (int) ($settings['timeout_seconds'] ?? 10) ?>" required>
                    </label>
                    <button type="submit">Simpan Pengaturan</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
