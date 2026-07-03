<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/functions.php';

$config = serviceAuditorConfig();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Auditor</title>
    <link rel="stylesheet" href="/assets/style.css">

    <script src="/assets/scripts/jquery.min.js"></script>
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

        <section class="filter-panel" id="filterPanel">
            <div class="filter-header">
                <h3>Filters</h3>
                <button id="toggleFilter" class="toggle-btn" title="Minimize">−</button>
            </div>
            <div class="filter-container" id="filterContainer">
                <div class="filter-group">
                    <label>Date Filter</label>
                    <select id="filterDate" class="filter-select">
                        <option value="all">All</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">A Week</option>
                        <option value="month">A Month</option>
                        <option value="year">A Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    <div id="customDateRange" class="custom-range" style="display: none; margin-top: 8px;">
                        <input type="date" id="dateFrom" placeholder="From">
                        <input type="date" id="dateTo" placeholder="To">
                    </div>
                </div>

                <div class="filter-group">
                    <label>Limit</label>
                    <select id="filterLimit" class="filter-select">
                        <option value="unlimited">∞ Unlimited</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select id="filterStatus" class="filter-select">
                        <option value="all">All</option>
                        <option value="success">Success</option>
                        <option value="error">Error</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Auto Reload</label>
                    <select id="autoReload" class="filter-select">
                        <option value="off">Off</option>
                        <option value="1m">1 minute</option>
                        <option value="5m">5 minutes</option>
                        <option value="10m">10 minutes</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button id="applyFilter" class="btn-apply">Apply Filter</button>
                </div>
            </div>
        </section>

        <main class="dashboard-grid">
            <section class="card stat-card success">
                <div class="stat-title">Success</div>
                <div class="stat-value" id="sum-success">-</div>
                <div class="stat-detail" id="sum-rate">-</div>
            </section>
            <section class="card stat-card error">
                <div class="stat-title">Error</div>
                <div class="stat-value" id="sum-err">-</div>
                <div class="stat-detail">Request yang gagal dipantau</div>
            </section>
            <section class="card stat-card total">
                <div class="stat-title">Total Checks</div>
                <div class="stat-value" id="sum-total">-</div>
                <div class="stat-detail">Log tersimpan di folder logs</div>
            </section>

            <section class="card wide-card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                    <span class="chip">Last 8 checks</span>
                </div>
                <div class="table-wrap custom-scrollbar" style="max-height: 500px; overflow-y: scroll;">
                    <table id="table-res">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Code</th>
                                <th>Latency</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="steps">
                    <a class="chip" href="/settings.php">Buka Settings</a>
                    <a class="chip" href="/run-check.php">Run Check Now</a>
                    <a class="chip" href="/runtime-log.php">Lihat Runtime Log</a>
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

    <div class="modal-overlay" id="detailModal">
        <div class="modal-card">
            <button class="modal-close" id="closeModal" aria-label="Close detail modal">×</button>
            <h3>Detail Check</h3>
            <div class="modal-body">
                <div class="detail-row"><span>Time</span><span id="detailTime">-</span></div>
                <div class="detail-row"><span>URL</span><span id="detailUrl">-</span></div>
                <div class="detail-row"><span>Status</span><span id="detailStatus">-</span></div>
                <div class="detail-row"><span>HTTP Code</span><span id="detailCode">-</span></div>
                <div class="detail-row"><span>Latency</span><span id="detailLatency">-</span></div>
                <div class="detail-row"><span>Error</span><code id="detailError">-</code></div>
                <div class="detail-row detail-full"><span>Body Excerpt</span><pre id="detailBody">-</pre></div>
            </div>
        </div>
    </div>
</body>
    <script src="/assets/scripts/script.js"></script>
    <script src="/assets/scripts/filter.js"></script>
</html>