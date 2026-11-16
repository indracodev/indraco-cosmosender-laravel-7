# CosmoSender Project Rules & Frontend Standards

Aturan spesifik proyek ini melengkapi dan memiliki prioritas tertinggi untuk pengembangan **CosmoSender (Laravel 7 + Bootstrap 5.3)**.

## 1. Standar Bootstrap 5.3 Default & Color Modes
- **Komponen & Utility Bawaan Bootstrap**: Selalu gunakan komponen dan *utility class* resmi sesuai dokumentasi Bootstrap 5.3 (`.card`, `.card-header`, `.card-body`, `.card-footer`, `.table`, `.btn-primary`, `.bg-body`, `.bg-body-tertiary`, `.text-body`, `.text-body-secondary`, `.border`, `.collapse`, `.modal`, dll.).
- **Hindari Custom Style Berlebihan**: Dilarang membuat *class* CSS kustom baru yang menduplikasi komponen Bootstrap (seperti `.cs-card`, `.cs-table`, `.btn-cs-primary`) atau menimpa variabel dasar `body` di luar sistem variabel `--bs-*`.
- **Mode Gelap / Terang (Color Modes)**: Gunakan mekanisme resmi Bootstrap 5.3 Color Modes (`data-bs-theme="light|dark|auto"`) beserta variabel CSS bawaan (`var(--bs-body-bg)`, `var(--bs-body-color)`, `var(--bs-border-color)`, `var(--bs-tertiary-bg)`).

## 2. 100% Native JavaScript (Tanpa jQuery)
- **Bebas jQuery**: Dilarang memuat atau menggunakan `jquery.min.js` (`$`, `jQuery`, `$.ajax`) di seluruh halaman aplikasi.
- **Standar Vanilla JS (ES6+)**:
  - Gunakan API DOM standar (`document.getElementById`, `document.querySelector`, `document.querySelectorAll`, `addEventListener`, `closest`).
  - Gunakan `fetch()` API (atau *helper* global `csRequest()`) dengan `FormData` / `JSON` dan header `X-CSRF-TOKEN` untuk seluruh komunikasi AJAX ke backend Laravel.
  - Gunakan instance JavaScript resmi Bootstrap 5 (`bootstrap.Modal.getOrCreateInstance()`, `bootstrap.Collapse`, `bootstrap.Alert`) untuk mengontrol komponen interaktif.

## 3. Pemanfaatan Maksimal Fitur Blade & Laravel 7
- **Arsitektur Layout Modular**: Gunakan pewarisan layout (`@extends`, `@section`, `@yield`, `@stack('styles')`, `@stack('scripts')`) melalui kerangka dasar `layouts.base`, `layouts.auth`, dan `layouts.app`.
- **Reusable Blade Components**: Manfaatkan Anonymous Blade Components (`<x-...>` pada `resources/views/components/`) untuk elemen UI berulang seperti tombol *theme switcher* (`<x-theme-toggle />`) dan input password dengan fitur *show/hide* (`<x-input-password />`).
- **Direktif & Helper Laravel**: Maksimalkan penggunaan `config('app.name')`, `@csrf`, `@error`, `old()`, `@json()`, dan helper `route()` di seluruh file Blade.

