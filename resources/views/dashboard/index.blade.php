@extends('layouts.app')

@section('title', 'Brand Workspace')
@section('page_title', 'Brand Workspace')

@section('content')
   <section aria-label="Daftar Brand Aktif">
      <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
         <div>
            <h1 class="fs-2 mb-0 fw-semibold">@yield('page_title', 'Workspace')</h1>
            <p class="text-body-secondary small mb-0">Pilih lini brand untuk mengelola materi konten HTML (EDM) atau memulai
               pengiriman email.</p>
         </div>
         <a href="{{ route('settings.brands') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-gear me-1"></i> Kelola Brand
         </a>
      </div>

      <div class="row g-3">
         @forelse ($brands as $brand)
            @php
               $isReady = $brand->isReadyToBlast();
               $waitMinutes = $brand->remainingWaitMinutes();
               $serviceServer = $brand->service && $brand->service->server ? $brand->service->server : null;
               $smtpHosts = $brand->activeBlastRules
                   ->map(function ($r) {
                       return $r->emailHost;
                   })
                   ->filter()
                   ->unique('id');
            @endphp
            <div class="col-12 col-xl-6">
               <article class="card h-100 shadow-sm">
                  <div class="card-header bg-transparent p-3 d-sm-flex align-items-start justify-content-between gap-2">
                     <div>
                        <div class="d-flex align-items-center gap-2">
                           <h2 class="h5 fw-bold mb-0">{{ $brand->name }}</h2>
                           @if ($isReady)
                              <span
                                 class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">Siap
                                 Blast</span>
                           @else
                              <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning-subtle"
                                 title="Jeda {{ $brand->blast_hour_interval }} jam dari blast terakhir">
                                 Jeda Tunggu ({{ $waitMinutes }} mnt)
                              </span>
                           @endif
                        </div>
                        <div class="text-body-secondary small mt-1">
                           Pengirim: <span class="fw-medium text-body">{{ $brand->email_alias ?: '-' }}</span>
                           <br>
                           Domain: <code>{{ $brand->domain }}</code>
                        </div>
                     </div>

                     <div class="small text-sm-end">
                        <div class="fw-semibold text-body">{{ number_format($brand->subscriber_count) }} Subscriber</div>
                        <div class="text-body-secondary" style="font-size: 0.75rem;">Kuota:
                           {{ $brand->blast_limit ?: 'Tanpa Batas' }} / batch</div>
                     </div>
                  </div>

                  <div class="card-body p-3">
                     <div class="row g-2 small">
                        <div class="col-12 col-sm-6">
                           <div class="text-body-secondary mb-1" style="font-size: 0.75rem;">Aturan Blast Aktif
                              ({{ $brand->activeBlastRules->count() }})
                           </div>
                           <div class="d-flex flex-wrap gap-1">
                              @forelse ($brand->activeBlastRules as $rule)
                                 <span
                                    class="badge {{ $rule->type === 'blast' ? 'text-bg-danger' : 'text-bg-secondary' }}">
                                    {{ $rule->name }} ({{ $rule->activeDetails->count() }} relay)
                                 </span>
                              @empty
                                 <span class="text-body-secondary fst-italic">Belum ada rule</span>
                              @endforelse
                           </div>
                        </div>

                        <div class="col-12 col-sm-6">
                           <div class="text-body-secondary mb-1" style="font-size: 0.75rem;">Content Service &amp; SMTP
                              Server</div>
                           <div class="d-flex flex-wrap gap-1">
                              @if ($serviceServer)
                                 <span class="badge text-dark border"
                                    style="background-color: {{ $brand->service->color ?: '#f1f5f9' }};">
                                    {{ $serviceServer->name }} / {{ $brand->service->service }}
                                 </span>
                              @endif
                              @foreach ($smtpHosts as $host)
                                 <span class="badge text-dark border"
                                    style="background-color: {{ optional($host->server)->color ?: '#e2e8f0' }};">
                                    {{ optional($host->server)->name }} / {{ $host->host }}
                                 </span>
                              @endforeach
                           </div>
                        </div>
                     </div>
                  </div>

                  <footer class="card-footer d-sm-flex column-gap-3 justify-content-between align-items-center">
                     <div class="text-body-secondary mb-2 mb-sm-0" style="font-size: 0.75rem;">
                        Blast Terakhir:
                        {{ $brand->last_blast_time && $brand->last_blast_time !== '0000-00-00 00:00:00' ? $brand->last_blast_time : '-' }}
                     </div>
                     <div class="d-flex gap-2 text-nowrap">
                        <a href="{{ route('contents.index', $brand->id) }}" class="btn btn-sm w-50 btn-outline-secondary">
                           <i class="bi bi-code-square me-1"></i> Content ({{ $brand->content_count }})
                        </a>
                        <a href="{{ route('blast.index', $brand->id) }}" class="btn btn-sm w-50 btn-primary">
                           <i class="bi bi-send-fill me-1"></i> Begin Blast
                        </a>
                     </div>
                  </footer>
               </article>
            </div>
         @empty
            <div class="col-12">
               <div class="card p-4 text-center text-body-secondary">
                  Belum ada Brand aktif. Silakan tambahkan atau aktifkan Brand pada menu pengaturan.
               </div>
            </div>
         @endforelse
      </div>
   </section>
@endsection
