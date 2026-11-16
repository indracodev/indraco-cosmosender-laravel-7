# Cosmosender — Laravel 7 Edition (`cosmosender-lv-7`)

Aplikasi Manajemen Konten EDM (*Electronic Direct Mail*) & *Round-Robin Multi-Relay Email Blasting* PT Indraco / Supresso Group berbasis **Laravel 7.30.7** (kompatibel penuh dengan **PHP 7.2**).

## Dokumentasi Lengkap Proyek
Silakan baca dua dokumen utama berikut di dalam direktori proyek ini untuk memahami konteks arsitektur, struktur database, kredensial, dan riwayat konversi:
1. **[DISKUSI_KONVERSI_LARAVEL7.md](./DISKUSI_KONVERSI_LARAVEL7.md)** — Dokumentasi hasil konversi Laravel 7, panduan 2 akun (`dev` vs `admin`), daftar optimasi, dan peta file aplikasi.
2. **[documentation.md](./documentation.md)** — Dokumentasi teknis sistem asli, ERD 22 tabel database `supresso_mailblast`, dan catatan server/relay SMTP.

## Akses Cepat Lokal
- **URL**: `http://localhost/PHP72/indracodev/cosmosender-lv-7`
- **Akun Lokal / Staging (`LOCAL / STAGING MODE`)**:
  - Username: `dev` | Password: `123`
- **Akun Produksi (`LIVE PRODUCTION MODE`)**:
  - Username: `admin` | Password: `123`
- **Perintah Artisan (PHP 7.2)**:
  ```bash
  /usr/bin/php7.2 artisan route:list
  /usr/bin/php7.2 artisan migrate
  ```
