@extends('layouts.base')

@section('body')
<div class="d-flex min-vh-100">
    <!-- Sidebar Navigation (Bootstrap 5.3 Responsive Offcanvas) -->
    <aside
        class="offcanvas-lg offcanvas-start flex-shrink-0"
        tabindex="-1"
        id="csSidebar"
        aria-labelledby="csSidebarLabel"
        style="--bs-offcanvas-width: 256px; width: 256px;"
    >
        <div class="bg-body border-end d-flex flex-column w-100 h-100 sticky-lg-top" style="max-height: 100vh;">
            <div class="px-3 border-bottom d-flex align-items-center justify-content-center" style="min-height: var(--app-header-height);">
                <a href="{{ route('dashboard') }}" class="link-body-emphasis text-decoration-none text-center" id="csSidebarLabel">
                    <img
                        src="{{ asset('assets/img/logo-indraco.png') }}"
                        data-img-src-light="{{ asset('assets/img/logo-indraco.png') }}"
                        data-img-src-dark="{{ asset('assets/img/logo-indraco-invert.png') }}"
                        alt="Logo Indraco"
                        style="height: 26px; width: auto;"
                    >
                </a>
                <button
                    type="button"
                    class="btn-close d-lg-none"
                    data-bs-dismiss="offcanvas"
                    data-bs-target="#csSidebar"
                    aria-label="Tutup Navigasi"
                ></button>
            </div>

            <nav class="nav nav-pills flex-column p-3 gap-1 flex-grow-1 overflow-y-auto flex-nowrap" aria-label="Menu Utama">
                <div class="text-uppercase text-body-secondary fw-bold px-2 py-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                    Operasional
                </div>
                <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('dashboard', 'contents.*', 'blast.*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Brand Workspace</span>
                </a>
                <a href="{{ route('emailsync.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('emailsync.*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-people"></i>
                    <span>Customer Email Sync</span>
                </a>

                <div class="text-uppercase text-body-secondary fw-bold px-2 pt-3 pb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                    Pengaturan Transaksi
                </div>
                <a href="{{ route('settings.brands') }}" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.brands*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-bookmark-star"></i>
                    <span>Brand Configuration</span>
                </a>
                <a href="{{ route('settings.blastrules') }}" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.blastrules*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-sliders"></i>
                    <span>Blast Rules &amp; Test</span>
                </a>

                <div class="text-uppercase text-body-secondary fw-bold px-2 pt-3 pb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                    Master Data
                </div>
                <a href="{{ route('settings.relays') }}" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.relays*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-envelope-at"></i>
                    <span>Email Relay (SMTP)</span>
                </a>
                <a href="{{ route('settings.hosts') }}" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.hosts*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-hdd-network"></i>
                    <span>SMTP Host</span>
                </a>
                <a href="{{ route('settings.services') }}" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.services*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-folder-symlink"></i>
                    <span>Content Services</span>
                </a>
                <a href="{{ route('settings.servers') }}" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.servers*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-server"></i>
                    <span>Server</span>
                </a>
                <div class="text-uppercase text-body-secondary fw-bold px-2 pt-3 pb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                    Contoh Data
                </div>
                <a href="#" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.servers*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-server"></i>
                    <span>Contoh Menu</span>
                </a>
                <a href="#" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.servers*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-server"></i>
                    <span>Contoh Menu</span>
                </a>
                <a href="#" class="nav-link d-flex align-items-center gap-2 py-2 px-3 {{ request()->routeIs('settings.servers*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                    <i class="bi bi-server"></i>
                    <span>Contoh Menu</span>
                </a>
            </nav>

            <div class="p-3 border-top mt-auto flex-shrink-0">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="text-truncate">
                        <div class="fw-semibold text-body small text-truncate">{{ $currentUser['real_name'] ?? 'Admin' }}</div>
                        <div class="text-body-secondary" style="font-size: 0.75rem;">{{ '@' . ($currentUser['user_name'] ?? 'admin') }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Keluar Aplikasi">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow-1 d-flex flex-column min-w-0 bg-body-tertiary">
        <header class="navbar bg-body border-bottom px-3 px-lg-4 sticky-top" style="min-height: var(--app-header-height);">
            <div class="d-flex align-items-center gap-2">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary d-lg-none"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#csSidebar"
                    aria-controls="csSidebar"
                    aria-label="Buka Navigasi"
                >
                    <i class="bi bi-list"></i>
                </button>
                {{-- <h1 class="h6 mb-0 fw-semibold">@yield('page_title', 'Workspace')</h1> --}}
                <div class="hstack gap-2">
                    <div class="fw-bold text-uppercase">{{ config('app.name') }}</div>
                    <div class="vr"></div>
                    <div>Manajemen Konten EDM &amp; Email Blasting</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if(!empty($isStagingMode))
                    <span class="badge rounded-pill text-bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 d-none" title="Akun Lokal/Staging: Upload tersimpan di lokal & Blast pelanggan otomatis berjalan dalam Mode Simulasi.">
                        <i class="bi bi-shield-lock-fill me-1"></i>
                        <span>LOCAL / STAGING MODE ({{ $currentUser['user_name'] }})</span>
                    </span>
                @else
                    <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1 d-none" title="Akun Produksi Live: Terhubung penuh ke mbcontentservices & pengiriman Blast Pelanggan Aktif.">
                        <i class="bi bi-broadcast me-1"></i>
                        <span>LIVE PRODUCTION MODE ({{ $currentUser['user_name'] }})</span>
                    </span>
                @endif
                <x-theme-toggle id="btnToggleTheme" />
            </div>
        </header>

        <main class="container-fluid p-3 p-lg-4 flex-grow-1" style="max-width: 1440px;">
            @yield('content')
        </main>
    </div>
</div>
@endsection
