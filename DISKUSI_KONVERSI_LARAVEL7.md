# Dokumentasi & Catatan Konversi Cosmosender ke Laravel 7 (`cosmosender-lv-7`)

**Tanggal Eksekusi**: 16 November 2026  
**Status Proyek**: **SELESAI DIBANGUN & TERVERIFIKASI (HTTP 200 OK)**  
**Proyek Asal (PHP Native)**: `/var/www/html/cosmosender`  
**Proyek Baru (Laravel 7 - PHP 7.2)**: `/var/www/PHP72/indracodev/cosmosender-lv-7`  
**URL Akses Lokal**: `http://localhost/PHP72/indracodev/cosmosender-lv-7`

---

## 1. Konteks Pengguna & Tujuan Proyek
- **Profil Pengguna**: UI/UX & Frontend Developer (mengutamakan tampilan yang bersih, modern, responsif, fokus pada fungsi utama, dan mudah dipelihara setelah rekan Backend resign).
- **Tujuan Utama**: Mengonversi aplikasi *email blasting* **Cosmosender** dari PHP Native ke **Framework Laravel 7** (`v7.30.7`) agar kompatibel dengan server hosting produksi yang menggunakan **PHP 7.2** (`7.2.34`), sekaligus memperbaiki *bug*, menutup celah keamanan, dan menyegarkan desain UI/UX.

---

## 2. Ringkasan Arsitektur Aplikasi (Pendekatan Awam Teknis)
**Cosmosender** adalah aplikasi internal pengelola materi promosi email (*Electronic Direct Mail* / EDM) dan pengiriman email massal ke *subscriber* dari berbagai *brand* PT Indraco (Supresso SG, Supresso ID, Indraco Store, Pandan Garden).

### 4 Alur Kerja Utama:
1. **Sinkronisasi Kontak Pelanggan (`Email Sync`)**:
   - Menarik data email pelanggan dari tabel sumber masing-masing *brand* (`m_email_sync_table`) ke tabel utama `mt_customer_email`.
   - Kontak yang sudah tidak aktif/berhenti berlangganan otomatis ditandai `flag = 'N'`.
2. **Manajemen Konten EDM (`Content Management`)**:
   - Membuat jadwal materi berdasarkan tanggal (`date_namespace`) dan jam (`time_namespace`), serta menyusun kode HTML newsletter menggunakan **CodeMirror**.
   - Menyediakan *placeholder* `{email}` di dalam kode HTML yang otomatis diganti dengan email masing-masing penerima saat dikirim (untuk kebutuhan *tracking pixel* dan *redirect link*).
   - Mengunggah gambar banner (*asset*) ke `t_asset` dan menyinkronkannya ke server konten eksternal (`mbcontentservices/` pada server `Solindo` atau `Niaga`).
3. **Aturan Pengiriman & Rotasi Akun Pengirim (`Blast Rules` & `Email Relay`)**:
   - Menyimpan **32 akun SMTP Relay** di tabel `m_email_relay` untuk menghindari pemblokiran spam (*rate limiting*).
   - Memiliki 2 tipe *rule* di `m_blast_rule`:
     - **`test` (`Send Test`)**: Mengirim sampel email ke daftar email internal/tester (`m_blast_rule_recipient`).
     - **`blast`**: Mengirim email promosi massal ke pelanggan asli di `mt_customer_email` sesuai kuota `blast_limit` per kloter (mulai dari urutan `last_email_id`).
4. **Mesin Pengirim Email (`Blast Engine`)**:
   - Mengirim email secara bergantian (*Round-Robin*) menggunakan daftar akun SMTP Relay pada *rule* yang dipilih, dilengkapi jeda waktu (*delay*) setiap putaran penuh serta pencatatan urutan ID email terakhir (`last_email_id`), waktu kirim terakhir (`last_blast_time`), dan log pengiriman (`t_blast_log`).

---

## 3. Konfigurasi Lingkungan & Database yang Sudah Diterapkan

1. **Lingkungan PHP 7.2 & Laravel 7**:
   - Direktori: `/var/www/PHP72/indracodev/cosmosender-lv-7`
   - Binary PHP: `/usr/bin/php7.2` (**PHP 7.2.34**, terhubung otomatis ke `php7.2-fpm` di Apache).
   - `composer.json`: Dikunci pada `"platform": { "php": "7.2.34" }`.
   - Subfolder Routing: Dilengkapi `.htaccess` root dan normalisasi `SCRIPT_NAME` di `public/index.php` sehingga dapat diakses langsung melalui `http://localhost/PHP72/indracodev/cosmosender-lv-7` maupun di domain root saat *production*.
2. **Database MySQL (`supresso_mailblast`)**:
   - File dump `/var/www/html/cosmosender/supresso_mailblast.sql` telah diimpor ke MySQL lokal (`DB_DATABASE=supresso_mailblast`, `DB_USERNAME=root`, `DB_PASSWORD=root`).
   - **Safe Non-Destructive Migration** (`database/migrations/2026_11_16_000001_create_cosmosender_tables.php`) telah dijalankan menggunakan pengaman `if (!Schema::hasTable(...))` sehingga seluruh data lama tetap utuh 100%.
3. **Asset Frontend Lokal (`public/assets/`)**:
   - Bootstrap `5.3.8` (`public/assets/vendor/bootstrap-5.3.8-dist`)
   - Bootstrap Icons `1.13.1` (`public/assets/vendor/bootstrap-icons-1.13.1`)
   - CodeMirror Editor (`public/assets/vendor/codemirror`) & jQuery (`public/assets/vendor/jquery.min.js`)
   - Stylesheet kustom bersih & responsif (`public/assets/css/cosmosender.css`)

---

## 4. Panduan Login 2 Akun (Mode Lokal/Staging vs Mode Live)

Aplikasi menyediakan **2 akun login** pada tabel `m_user` dengan perilaku otomatis:

| Jenis Akun | Username | Password | Perilaku & Pengaman Otomatis |
| :--- | :--- | :--- | :--- |
| **Akun Lokal / Tester (Staging Mode)** | `dev` | `123` | - Ditandai *badge* kuning **LOCAL / STAGING MODE (dev)** di topbar.<br>- Upload gambar & simpan HTML disimpan ke folder lokal proyek (`public/uploads/...`) terlebih dahulu.<br>- Fitur **`Send Test`** dapat mengirim email sungguhan via SMTP ke email tester (matikan saklar *Mode Simulasi* saat ingin kirim sungguhan ke email tester sendiri).<br>- Fitur **`Blast`** (ke ribuan pelanggan asli) **otomatis terkunci pada Mode Simulasi (Sandbox)** sehingga aman untuk mengetes *Progress Bar* dan putaran *relay* tanpa benar-benar mengirim email keluar ke pelanggan dan tanpa mengubah `last_email_id` asli.<br>- Menyediakan tombol **"Push Materi ke Server Live"** di Editor HTML untuk menyinkronkan HTML & gambar lokal ke endpoint `mbcontentservices/`. |
| **Akun Live (Production Mode)** | `admin` | `123` | - Ditandai *badge* hijau **LIVE PRODUCTION MODE (admin)** di topbar.<br>- Penyimpanan konten & upload gambar langsung terhubung ke endpoint `mbcontentservices/` server live (`Solindo` / `Niaga`).<br>- Pengiriman **`Blast`** ke pelanggan asli (`mt_customer_email`) aktif sepenuhnya dan memperbarui `last_email_id` serta `t_blast_log` secara *real-time*. |

---

## 5. Daftar Optimasi & Perbaikan Bug yang Telah Diimplementasikan

1. **Perbaikan Bug Kalkulasi Interval Waktu Blast (`App\Models\Brand::isReadyToBlast()`)**:
   - `blast_hour_interval` (satuan **Jam**) kini dikalikan `60` menit secara akurat menggunakan `Carbon` saat dibandingkan dengan `last_blast_time`, dilengkapi indikator sisa menit waktu tunggu di UI.
2. **Penutupan Kebocoran Password SMTP (`App\Services\SmtpBlastService` & `BlastController`)**:
   - Password asli dari 32 akun SMTP (`m_email_relay`) disembunyikan (`$hidden = ['password']`) dan tidak pernah dikirim ke JSON/HTML browser. Pengambilan kredensial SMTP dilakukan 100% di sisi server.
3. **Eksekusi Blasting Bertahap Anti-Timeout dengan Progress Bar Real-time (`resources/views/blast/index.blade.php`)**:
   - Pengiriman dilakukan per item/putaran melalui AJAX dengan *Progress Bar* interaktif, penghitung Sukses/Gagal, informasi akun SMTP Relay yang sedang bertugas (*Round-Robin*), jeda otomatis 5 detik per putaran penuh relay, serta tombol **Hentikan** (*Stop*).
4. **Eliminasi *N+1 Query* & *Looping Update* (`DashboardController`, `BlastController`, `EmailSyncController`)**:
   - Seluruh halaman menggunakan Eloquent *Eager Loading* (`with()`, `withCount()`) dan *Bulk Query* (`whereIn()->update()`, `insert()`) di dalam `DB::transaction()`.
5. **Fitur *Live HTML Preview* 3-Panel pada Editor Materi (`resources/views/content/index.blade.php`)**:
   - Editor materi menampilkan **Asset Gambar Banner** (kiri), **CodeMirror HTML Editor** (tengah), dan **Live HTML Preview `<iframe>`** (kanan) yang otomatis diperbarui saat kode HTML diketik.
6. **Pencatatan Riwayat Blasting (`t_blast_log`)**:
   - Setiap eksekusi pengiriman (baik `completed`, `test_sent`, maupun `simulated`) tercatat rapi di tabel `t_blast_log` dan ditampilkan pada bagian bawah halaman *Begin Blast*.
7. **Kompatibilitas Penuh PHP 7.2**:
   - Fungsi `hrtime(true)` (PHP 7.3+) pada upload gambar telah diganti dengan `microtime(true)` di `ContentSyncService.php` sehingga berjalan bebas error di PHP 7.2.

---

## 6. Peta File Utama di `/var/www/PHP72/indracodev/cosmosender-lv-7`

- **Konfigurasi & Bootstrap**:
  - `.env` — Konfigurasi database `supresso_mailblast` (`root`/`root`) & kunci enkripsi `COSMOSENDER_ENCRYPT_KEY`.
  - `public/index.php` & `.htaccess` — Entrypoint dengan dukungan akses sub-direktori Apache PHP 7.2.
- **Database Migration & Models (`app/Models/`)**:
  - `database/migrations/2026_11_16_000001_create_cosmosender_tables.php`
  - `BaseCosmosenderModel.php`, `UserAccount.php`, `UserToken.php`, `Brand.php`, `Server.php`, `Service.php`, `EmailHost.php`, `EmailRelay.php`, `BlastRule.php`, `BlastRuleDetail.php`, `BlastRuleRecipient.php`, `EmailSyncTable.php`, `CustomerEmail.php`, `Content.php`, `Asset.php`, `BlastLog.php`
- **Services & Middleware**:
  - `app/Services/CosmosenderCipherService.php` — Enkripsi & dekripsi `AES-128-CTR`.
  - `app/Services/ContentSyncService.php` — Penyimpanan HTML & Asset Dual-Mode (Lokal & Push ke Server Live `mbcontentservices/`).
  - `app/Services/SmtpBlastService.php` — Pengiriman email via PHPMailer SSL Port 465 & Mode Simulasi.
  - `app/Http/Middleware/CosmosenderAuth.php` — Proteksi sesi login & pembagian state mode `dev` vs `admin`.
- **Controllers (`app/Http/Controllers/`) & Routes (`routes/web.php`)**:
  - `AuthController.php` — Login & Logout
  - `DashboardController.php` — Brand Workspace
  - `ContentController.php` — Content Management, CodeMirror Editor, Upload Asset, Push to Live, & Preview
  - `BlastController.php` — Begin Blast, Load Recipients, & Round-Robin Batch Sender
  - `EmailSyncController.php` — Monitoring Subscriber (Server-Side Pagination & Search), Config Sync, & Batch Sync
  - `BrandController.php` — CRUD Brand Configuration
  - `BlastRuleController.php` — CRUD Blast Rules, Assign/Remove Relays, & Add/Remove Test Recipients
  - `MasterSettingController.php` — CRUD Email Relays, SMTP Hosts, Content Services, & Servers
- **Views (`resources/views/`)**:
  - `layouts/app.blade.php` & `auth/login.blade.php`
  - `dashboard/index.blade.php`
  - `content/index.blade.php`
  - `blast/index.blade.php`
  - `emailsync/index.blade.php`
  - `settings/brands.blade.php`, `settings/blastrules.blade.php`, `settings/relays.blade.php`, `settings/hosts.blade.php`, `settings/services.blade.php`, `settings/servers.blade.php`
