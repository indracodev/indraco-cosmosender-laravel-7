@extends('layouts.auth')

@section('title', 'Masuk')

@push('styles')
   <style>
      body {
         background-image: url({{ asset('assets/img/bg-indraco-factory-bambe.jpg') }});
         background-repeat: no-repeat;
         background-position: center;
         background-size: cover;
         position: relative;

         &::after {
            content: "";
            width: 100%;
            height: 100%;
            background-image: radial-gradient(transparent, var(--bs-body-bg));
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(1.05);
            backdrop-filter: blur(2px);
            z-index: 0;
         }
      }

      .btn-open-guide {
         position: relative;
         width: 36px;
         height: 36px;

         & .bi {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(0);
            transition: .3s ease;
         }

         &.collapsed .bi {
            transform: translate(-50%, -50%) rotate(-180deg);
         }
      }
   </style>
@endpush

@section('content')
   <div class="position-fixed top-0 end-0 p-3 z-2">
      <x-theme-toggle class="btn rounded-0 p-0 border-0 shadow-none" />
   </div>
   <main class="z-1" style="width: 100%; max-width: 400px;">
      <section class="card p-3 p-sm-4 position-relative shadow vstack" style="max-height: calc(100dvh - 2rem);">

         <header class="text-center mb-4">
            <img src="{{ asset('assets/img/logo-indraco.png') }}"
               data-img-src-light="{{ asset('assets/img/logo-indraco.png') }}"
               data-img-src-dark="{{ asset('assets/img/logo-indraco-invert.png') }}" alt="Logo Indraco"
               style="height: 36px;" class="mb-2">
            <h1 class="h5 fw-bold visually-hidden">{{ config('app.name') }}</h1>
            <p class="text-body-secondary mb-0">Manajemen Konten EDM &amp; Email Blasting</p>
         </header>

         @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 small" role="alert">
               <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
            </div>
         @endif

         @if (session('status'))
            <div class="alert alert-success py-2 px-3 small" role="alert">
               <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
            </div>
         @endif

         <form action="{{ route('login.submit') }}" method="POST" novalidate class="flex-grow-1 vstack row-gap-3">
            @csrf
            <div>
               <label for="username" class="form-label small fw-semibold">
                  Nama Pengguna (Username) :
               </label>
               <input type="text" id="username" name="username" value="{{ old('username') }}"
                  class="form-control @error('username') is-invalid @enderror"
                  placeholder="Masukkan username (dev / admin)" required autofocus>
            </div>

            <div>
               <label for="password" class="form-label small fw-semibold">
                  Kata Sandi (Password) :
               </label>
               <x-input-password id="password" name="password" placeholder="Masukkan password" :required="true" />
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 my-3">
               <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Aplikasi
            </button>
         </form>

         <footer>
            <div class="hstack">
               <hr class="w-100">
               <button class="btn-open-guide btn rounded-0 border-0 shadow-none p-2 collapsed" type="button"
                  data-bs-toggle="collapse" data-bs-target="#coldet" aria-expanded="false" aria-controls="coldet">
                  <i class="bi bi-chevron-up"></i>
               </button>
            </div>
            <div class="collapse" id="coldet">
               <div class="small text-body-secondary mb-1 fw-semibold">Panduan Mode Akun:</div>
               <div class="d-flex flex-wrap column-gap-3">
                  <span><code>dev</code> : Mode Lokal / Simulasi</span>
                  <span><code>admin</code> : Mode Produksi Live</span>
               </div>
               <hr>
            </div>

            <p class="text-center text-muted small mb-0">
               Dikembangkan dengan ❤️ oleh INDRACODEV. &copy; {{ date('Y') }} <span
                  class="text-uppercase fw-bold">{{ config('app.name') }}</span>. Seluruh hak dilindungi.
            </p>
         </footer>
      </section>
   </main>
@endsection
