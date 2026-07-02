# Service Auditor

Service Auditor adalah aplikasi PHP native sederhana untuk memantau status layanan backend melalui cURL secara berkala. Aplikasi ini menyimpan hasil pengecekan ke file log per URL di folder logs, lalu menampilkan ringkasan di dashboard web yang modern dan responsif.

## Fitur

- Dashboard sederhana dengan ringkasan request success/error
- Cron job untuk menjalankan pengecekan service secara berkala
- Hasil pengecekan disimpan ke file log dengan struktur folder per URL
- Halaman settings untuk mengubah URL target, interval, dan jadwal cron tanpa hardcode
- UI responsif dan clean untuk desktop maupun mobile

## Struktur Project

```text
service-auditor/
├── app/
│   ├── config.php
│   ├── functions.php
│   └── settings.json
├── public/
│   ├── assets/
│   │   └── style.css
│   ├── index.php
│   └── settings.php
├── logs/
├── cron.php
├── docker-compose.yml
└── README.md
```

## Persiapan

### Opsi 1: Pakai Docker (tanpa instalasi PHP lokal)

1. Pastikan Docker dan Docker Compose terinstall.
2. Jalankan:

```bash
docker compose up -d
```

3. Buka browser ke:

```text
http://localhost:8080/
```

### Opsi 2: Pakai PHP lokal

1. Pastikan PHP 8 terinstall.
2. Pastikan ekstensi cURL aktif.
3. Jalankan aplikasi melalui web server Anda, misalnya Apache atau Nginx, dengan document root mengarah ke folder public.

## Konfigurasi

Pengaturan disimpan di file [app/settings.json](app/settings.json). Anda juga bisa mengubahnya melalui halaman Settings di aplikasi.

Nilai yang bisa diatur:

- check_url: URL target yang akan dicek
- check_interval_minutes: interval pengecekan dalam menit
- check_schedule: jadwal cron yang ditampilkan di UI
- timeout_seconds: batas timeout request

## Menjalankan Cron

Jika menggunakan PHP lokal, tambahkan entry cron berikut:

```bash
*/5 * * * * php /path/to/service-auditor/cron.php >> /dev/null 2>&1
```

Jika menggunakan Docker, Anda bisa menjalankan perintah di container:

```bash
docker compose exec app php /var/www/html/cron.php
```

## Menjalankan Dashboard

- Dashboard: http://localhost:8080/
- Settings: http://localhost:8080/settings.php

## Catatan

- Hasil log disimpan di folder logs dengan struktur: logs/(url)/(file)
- File log menggunakan ekstensi .log agar lebih hemat resource dibandingkan JSON penuh
- Dashboard membaca hasil log dari folder logs dan menampilkan data terbaru
