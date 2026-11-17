@extends('layouts.base')

@section('body')
   <!-- Wrapper -->
   <div id="csWrapper" class="d-flex">

      <!-- Sidebar Navigation (Bootstrap 5.3 Responsive Offcanvas) -->
      <aside class="flex-shrink-0 border-end">
         <div class="offcanvas-lg offcanvas-start vstack h-100" tabindex="-1" id="csSidebar" aria-labelledby="csSidebarLabel"
            style="--bs-offcanvas-width: 256px; width: 256px; max-height: 100vh;">

            <div>
               <div class="py-2 px-3 d-flex justify-content-lg-center align-items-lg-center border-bottom"
                  style="min-height: var(--app-header-height);">
                  <div class="vstack d-lg-none">
                     <span class="fw-bold text-uppercase">{{ config('app.name') }}</span>
                     <span class="small text-muted">Manajemen Konten EDM &amp; Email Blasting</span>
                  </div>

                  <button type="button" class="btn-close rounded-0 border-0 shadow-none d-lg-none"
                     data-bs-toggle="offcanvas" data-bs-target="#csSidebar" aria-controls="csSidebar"
                     aria-label="Tutup Navigasi"></button>


                  <a href="{{ route('dashboard') }}" class="navbar-brand m-0 d-none d-lg-flex" id="csSidebarLabel">
                     <img src="{{ asset('assets/img/logo-indraco.png') }}"
                        data-img-src-light="{{ asset('assets/img/logo-indraco.png') }}"
                        data-img-src-dark="{{ asset('assets/img/logo-indraco-invert.png') }}" alt="Logo Indraco"
                        style="height: 30px; width: auto;">
                  </a>
               </div>
            </div>

            <div class="p-3 flex-grow-1 overflow-x-hidden overflow-y-auto">
               <nav class="nav nav-pills flex-column gap-1 flex-nowrap" aria-label="Menu Utama">
                  <div class="text-uppercase text-body-secondary fw-bold px-2 py-1"
                     style="font-size: 0.7rem; letter-spacing: 0.05em;">
                     Operasional
                  </div>
                  <a href="{{ route('dashboard') }}"
                     class="nav-link d-flex align-items-center gap-2 px-3 {{ request()->routeIs('dashboard', 'contents.*', 'blast.*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                     <i class="bi bi-grid-1x2"></i>
                     <span>Brand Workspace</span>
                  </a>
                  <a href="{{ route('emailsync.index') }}"
                     class="nav-link d-flex align-items-center gap-2 px-3 {{ request()->routeIs('emailsync.*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                     <i class="bi bi-people"></i>
                     <span>Customer Email Sync</span>
                  </a>

                  <div class="text-uppercase text-body-secondary fw-bold px-2 pt-3 pb-1"
                     style="font-size: 0.7rem; letter-spacing: 0.05em;">
                     Pengaturan Transaksi
                  </div>
                  <a href="{{ route('settings.brands') }}"
                     class="nav-link d-flex align-items-center gap-2 px-3 {{ request()->routeIs('settings.brands*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                     <i class="bi bi-bookmark-star"></i>
                     <span>Brand Configuration</span>
                  </a>
                  <a href="{{ route('settings.blastrules') }}"
                     class="nav-link d-flex align-items-center gap-2 px-3 {{ request()->routeIs('settings.blastrules*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                     <i class="bi bi-sliders"></i>
                     <span>Blast Rules &amp; Test</span>
                  </a>

                  <div class="text-uppercase text-body-secondary fw-bold px-2 pt-3 pb-1"
                     style="font-size: 0.7rem; letter-spacing: 0.05em;">
                     Master Data
                  </div>
                  <a href="{{ route('settings.relays') }}"
                     class="nav-link d-flex align-items-center gap-2 px-3 {{ request()->routeIs('settings.relays*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                     <i class="bi bi-envelope-at"></i>
                     <span>Email Relay (SMTP)</span>
                  </a>
                  <a href="{{ route('settings.hosts') }}"
                     class="nav-link d-flex align-items-center gap-2 px-3 {{ request()->routeIs('settings.hosts*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                     <i class="bi bi-hdd-network"></i>
                     <span>SMTP Host</span>
                  </a>
                  <a href="{{ route('settings.services') }}"
                     class="nav-link d-flex align-items-center gap-2 px-3 {{ request()->routeIs('settings.services*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                     <i class="bi bi-folder-symlink"></i>
                     <span>Content Services</span>
                  </a>
                  <a href="{{ route('settings.servers') }}"
                     class="nav-link d-flex align-items-center gap-2 px-3 {{ request()->routeIs('settings.servers*') ? 'active fw-semibold' : 'link-body-emphasis' }}">
                     <i class="bi bi-server"></i>
                     <span>Server</span>
                  </a>
               </nav>
            </div>

            <div class="py-2 px-3 d-none">
               <form action="{{ route('logout') }}" method="POST" class="m-0">
                  @csrf
                  <button type="submit" class="btn w-100 btn-danger" title="Keluar Aplikasi">
                     <i class="bi bi-box-arrow-right"></i> Keluar
                  </button>
               </form>
            </div>

         </div>
      </aside>

      <!-- Main Content Area -->
      <div class="svh-100 flex-grow-1 vstack bg-body-tertiary" style="min-width: 0;">
         <header class="navbar navbar-expand-lg bg-body border-bottom" style="min-height: var(--app-header-height);">
            <div class="container-fluid column-gap-3">

               <button class="navbar-toggler rounded-0 p-1 shadow-none" type="button" data-bs-toggle="offcanvas"
                  data-bs-target="#csSidebar" aria-controls="csSidebar" aria-label="Buka Navigasi">
                  <span class=" navbar-toggler-icon"></span>
               </button>

               <a href="{{ route('dashboard') }}" class="navbar-brand me-auto d-flex d-lg-none" id="csSidebarLabel">
                  <img src="{{ asset('assets/img/logo-indraco.png') }}"
                     data-img-src-light="{{ asset('assets/img/logo-indraco.png') }}"
                     data-img-src-dark="{{ asset('assets/img/logo-indraco-invert.png') }}" alt="Logo Indraco"
                     style="height: 26px; width: auto;">
               </a>

               <div class="me-auto d-none d-lg-block">
                  <div class="hstack gap-2">
                     <div class="fw-bold text-uppercase">{{ config('app.name') }}</div>
                     <div class="vr"></div>
                     <div class="text-muted">Manajemen Konten EDM &amp; Email Blasting</div>
                  </div>
               </div>

               <x-theme-toggle id="btnToggleTheme" />
               <div class="dropdown">
                  <button class="btn rounded-0 border-0 shadow-none dropdown-toggle" data-bs-toggle="dropdown"
                     data-bs-target="#dropdownUser">
                     <i class="bi bi-person-circle"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end px-3" id="dropdownUser">
                     <div class="text-truncate mb-3">
                        <div class="fw-semibold text-body small text-truncate">{{ $currentUser['real_name'] ?? 'Admin' }}
                        </div>
                        <div class="text-body-secondary" style="font-size: 0.75rem;">
                           {{ '@' . ($currentUser['user_name'] ?? 'admin') }}</div>
                     </div>
                     <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm w-100 btn-danger" title="Keluar Aplikasi">
                           <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                     </form>
                  </div>
               </div>

            </div>
         </header>

         <div class="flex-grow-1 overflow-y-auto vstack">
            <main class="flex-grow-1">
               <div class="container-fluid py-4 p-xxl-5">
                  @yield('content')
               </div>
            </main>

            <footer>
               <div class="container-fluid">
                  <hr>
                  <p class="small text-muted text-center text-lg-start">
                     Dikembangkan dengan ❤️ oleh INDRACODEV. &copy; {{ date('Y') }}
                     <span class="text-uppercase fw-bold">{{ config('app.name') }}</span>. Seluruh hak dilindungi.
                  </p>
               </div>
            </footer>
         </div>
      </div>

   </div>
@endsection
