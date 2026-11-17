@extends('layouts.app')

@section('title', 'Customer Email Sync')
@section('page_title', 'Customer Email Sync')

@section('content')
   <h1 class="fs-2 mb-3 fw-semibold">@yield('page_title', 'Workspace')</h1>

   <section class="card shadow-sm" aria-label="Sinkronisasi Email Pelanggan per Brand">
      <div class="card-header bg-transparent py-3">
         <h2 class="h6 fw-bold mb-0">Basis Kontak Email Subscriber</h2>
         <p class="text-body-secondary small mb-0">Kelola konfigurasi pemetaan tabel sumber pelanggan dan pantau daftar
            email aktif per brand.</p>
      </div>

      <div class="table-responsive">
         <table class="table table-hover mb-0">
            <thead class="table-light">
               <tr>
                  <th style="width: 50px;">#</th>
                  <th>Brand</th>
                  <th>Tabel Sumber &amp; Filter</th>
                  <th>Sinkronisasi Terakhir</th>
                  <th>Subscriber Aktif</th>
                  <th class="text-end" style="width: 240px;">Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($brands as $index => $brand)
                  @php
                     $cfg = $brand->emailSyncTable;
                  @endphp
                  <tr>
                     <td class="text-body-secondary">{{ $brands->firstItem() + $index }}</td>
                     <td>
                        <div class="fw-semibold">{{ $brand->name }}</div>
                        <div class="text-body-secondary small"><code>{{ $brand->slug }}</code></div>
                     </td>
                     <td>
                        @if ($cfg && $cfg->table_name)
                           <div>Tabel: <code>{{ $cfg->table_name }}</code> (Kolom: <code>{{ $cfg->email_column }}</code>)
                           </div>
                           @if ($cfg->where_clause)
                              <div class="text-body-secondary small">Filter:
                                 <code>{{ $cfg->where_clause }} = '{{ $cfg->where_value }}'</code></div>
                           @endif
                        @else
                           <span class="text-body-secondary fst-italic small">Belum dikonfigurasi</span>
                        @endif
                     </td>
                     <td><code>{{ $brand->email_last_sync ?: '-' }}</code></td>
                     <td>
                        <span
                           class="badge bg-body-tertiary text-body border fw-semibold">{{ number_format($brand->subscriber_count) }}
                           email</span>
                     </td>
                     <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                           <button type="button" class="btn btn-outline-primary"
                              data-action="open-subscribers"
                              data-brand-id="{{ $brand->id }}"
                              data-brand-name="{{ $brand->name }}"
                              data-brand-slug="{{ $brand->slug }}">
                              <i class="bi bi-card-list me-1"></i> Daftar Email
                           </button>
                           <button type="button" class="btn btn-outline-secondary"
                              data-action="open-sync-config"
                              data-brand-id="{{ $brand->id }}"
                              data-brand-name="{{ $brand->name }}"
                              data-table-name="{{ optional($cfg)->table_name }}"
                              data-email-col="{{ optional($cfg)->email_column }}"
                              data-name-col="{{ optional($cfg)->name_column }}"
                              data-where-clause="{{ optional($cfg)->where_clause }}"
                              data-where-value="{{ optional($cfg)->where_value }}">
                              <i class="bi bi-gear me-1"></i> Config
                           </button>
                        </div>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>

      @if ($brands->hasPages())
         <div class="card-footer bg-transparent py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="small text-body-secondary">
               Menampilkan {{ $brands->firstItem() }}–{{ $brands->lastItem() }} dari {{ $brands->total() }} brand
            </div>
            <div class="m-0">
               {{ $brands->links() }}
            </div>
         </div>
      @endif
   </section>

   <!-- Modal Daftar Email & Tombol Sync -->
   <div class="modal fade" id="modalSubscribers" tabindex="-1" aria-labelledby="modalSubscribersLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalSubscribersLabel">Daftar Subscriber</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <input type="hidden" id="activeSyncBrandId">
               <div id="syncModalAlert"></div>

               <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                  <div class="d-flex gap-2">
                     <input type="search" id="searchSubscriberInput" class="form-control form-control-sm"
                        placeholder="Cari email / nama..." style="width: 220px;">
                     <select id="filterSubscriberFlag" class="form-select form-select-sm" style="width: 145px;">
                        <option value="Y">Aktif (Subscribed)</option>
                        <option value="N">Non-Aktif</option>
                     </select>
                  </div>

                  <div class="d-flex gap-2">
                     <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btnSyncMembership"
                        data-sync-mode="membership">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync Membership
                     </button>
                     <button type="button" class="btn btn-sm btn-primary" id="btnSyncRegular"
                        data-sync-mode="regular">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync dari Database Brand
                     </button>
                  </div>
               </div>

               <div class="table-responsive">
                  <table class="table table-sm mb-0">
                     <thead class="table-light">
                        <tr>
                           <th>ID</th>
                           <th>Email</th>
                           <th>Nama</th>
                           <th>Status Blast</th>
                           <th>Langganan</th>
                        </tr>
                     </thead>
                     <tbody id="subscriberTableBody"></tbody>
                  </table>
               </div>

               <div class="d-flex justify-content-between align-items-center mt-2 small">
                  <span id="subscriberPageInfo" class="text-body-secondary"></span>
                  <div class="btn-group btn-group-sm">
                     <button type="button" class="btn btn-outline-secondary" id="btnPrevPage">Sebelumnya</button>
                     <button type="button" class="btn btn-outline-secondary" id="btnNextPage">Berikutnya</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Modal Konfigurasi Tabel Sinkronisasi -->
   <div class="modal fade" id="modalSyncConfig" tabindex="-1" aria-labelledby="modalSyncConfigLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formSyncConfig">
            <input type="hidden" id="configBrandId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalSyncConfigLabel">Konfigurasi Tabel Sinkronisasi</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="cfgTableName" class="form-label small fw-semibold">Nama Tabel Sumber</label>
                  <input type="text" id="cfgTableName" name="table_name" class="form-control"
                     placeholder="Contoh: mailblastid" required>
               </div>
               <div class="row g-2 mb-3">
                  <div class="col-6">
                     <label for="cfgEmailCol" class="form-label small fw-semibold">Kolom Email</label>
                     <input type="text" id="cfgEmailCol" name="email_column" class="form-control"
                        placeholder="Contoh: email" required>
                  </div>
                  <div class="col-6">
                     <label for="cfgNameCol" class="form-label small fw-semibold">Kolom Nama</label>
                     <input type="text" id="cfgNameCol" name="name_column" class="form-control"
                        placeholder="Contoh: nama">
                  </div>
               </div>
               <div class="row g-2">
                  <div class="col-6">
                     <label for="cfgWhereClause" class="form-label small fw-semibold">Kolom Filter (Where)</label>
                     <input type="text" id="cfgWhereClause" name="where_clause" class="form-control"
                        placeholder="Contoh: subrek">
                  </div>
                  <div class="col-6">
                     <label for="cfgWhereValue" class="form-label small fw-semibold">Nilai Filter</label>
                     <input type="text" id="cfgWhereValue" name="where_value" class="form-control"
                        placeholder="Contoh: subscribed">
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">
                  <i class="bi bi-check-lg me-1"></i> Simpan Konfigurasi
               </button>
            </div>
         </form>
      </div>
   </div>
@endsection

@push('scripts')
   <script>
      let currentSubPage = 1;
      let lastSubPage = 1;
      let searchDebounce = null;

      const activeSyncBrandIdEl = document.getElementById('activeSyncBrandId');
      const searchSubscriberInputEl = document.getElementById('searchSubscriberInput');
      const filterSubscriberFlagEl = document.getElementById('filterSubscriberFlag');
      const subscriberTableBodyEl = document.getElementById('subscriberTableBody');
      const subscriberPageInfoEl = document.getElementById('subscriberPageInfo');
      const btnPrevPageEl = document.getElementById('btnPrevPage');
      const btnNextPageEl = document.getElementById('btnNextPage');
      const btnSyncMembershipEl = document.getElementById('btnSyncMembership');
      const btnSyncRegularEl = document.getElementById('btnSyncRegular');
      const syncModalAlertEl = document.getElementById('syncModalAlert');

      function openSubscriberModal(brandId, brandName, slug) {
         activeSyncBrandIdEl.value = brandId;
         document.getElementById('modalSubscribersLabel').textContent = 'Daftar Subscriber — ' + brandName;
         syncModalAlertEl.innerHTML = '';

         btnSyncMembershipEl.classList.toggle('d-none', slug !== 'supresso-sg');

         currentSubPage = 1;
         showModal('modalSubscribers');
         fetchSubscribers();
      }

      async function fetchSubscribers() {
         const brandId = activeSyncBrandIdEl.value;
         const search = searchSubscriberInputEl.value;
         const flag = filterSubscriberFlagEl.value;

         subscriberTableBodyEl.innerHTML =
            '<tr><td colspan="5" class="text-center text-body-secondary py-3">Memuat data...</td></tr>';

         try {
            const res = await csRequest(`{{ url('email-sync') }}/${brandId}/customers`, {
               method: 'GET',
               data: {
                  page: currentSubPage,
                  search: search,
                  flag: flag
               }
            });

            const p = res.data;
            const items = p.data || [];
            currentSubPage = p.current_page;
            lastSubPage = p.last_page;

            subscriberPageInfoEl.textContent =
               `Halaman ${p.current_page} dari ${p.last_page} (Total ${p.total} kontak)`;
            btnPrevPageEl.disabled = p.current_page <= 1;
            btnNextPageEl.disabled = p.current_page >= p.last_page;

            if (!items.length) {
               subscriberTableBodyEl.innerHTML =
                  '<tr><td colspan="5" class="text-center text-body-secondary py-3">Tidak ada data email yang sesuai filter.</td></tr>';
               return;
            }

            let rows = '';
            items.forEach((item) => {
               const statusBadge = item.flag === 'Y' ?
                  '<span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">Subscribed</span>' :
                  '<span class="badge text-bg-secondary">Unsubscribed</span>';

               rows += `
                    <tr>
                        <td><code>#${item.id}</code></td>
                        <td class="fw-medium">${item.email}</td>
                        <td>${item.nama || '-'}</td>
                        <td><span class="badge bg-body-tertiary text-body border">${item.status_blast || '-'}</span></td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
            subscriberTableBodyEl.innerHTML = rows;
         } catch (err) {
            subscriberTableBodyEl.innerHTML =
               `<tr><td colspan="5" class="text-center text-danger py-3">${err.message}</td></tr>`;
         }
      }

      searchSubscriberInputEl.addEventListener('input', () => {
         clearTimeout(searchDebounce);
         searchDebounce = setTimeout(() => {
            currentSubPage = 1;
            fetchSubscribers();
         }, 300);
      });

      filterSubscriberFlagEl.addEventListener('change', () => {
         currentSubPage = 1;
         fetchSubscribers();
      });

      btnPrevPageEl.addEventListener('click', () => {
         if (currentSubPage > 1) {
            currentSubPage--;
            fetchSubscribers();
         }
      });

      btnNextPageEl.addEventListener('click', () => {
         if (currentSubPage < lastSubPage) {
            currentSubPage++;
            fetchSubscribers();
         }
      });

      async function triggerRemoteSync(mode) {
         const brandId = activeSyncBrandIdEl.value;
         if (mode === 'membership' && !confirm(
               'Sinkronisasi membership akan memperbarui daftar aktif hanya untuk member. Lanjutkan?')) {
            return;
         }

         btnSyncRegularEl.disabled = true;
         btnSyncMembershipEl.disabled = true;
         syncModalAlertEl.innerHTML =
            '<div class="alert alert-info py-2 px-3 small">Sedang menghubungi server sumber dan menyinkronkan data...</div>';

         try {
            const res = await csRequest(`{{ url('email-sync') }}/${brandId}/sync`, {
               method: 'POST',
               data: {
                  mode: mode
               }
            });
            btnSyncRegularEl.disabled = false;
            btnSyncMembershipEl.disabled = false;
            syncModalAlertEl.innerHTML = `<div class="alert alert-success py-2 px-3 small">${res.message}</div>`;
            fetchSubscribers();
         } catch (err) {
            btnSyncRegularEl.disabled = false;
            btnSyncMembershipEl.disabled = false;
            syncModalAlertEl.innerHTML = `<div class="alert alert-warning py-2 px-3 small">${err.message}</div>`;
         }
      }

      function openSyncConfigModal(brandId, brandName, cfg) {
         document.getElementById('configBrandId').value = brandId;
         document.getElementById('modalSyncConfigLabel').textContent = 'Konfigurasi Sync — ' + brandName;
         document.getElementById('cfgTableName').value = cfg ? (cfg.table_name || '') : '';
         document.getElementById('cfgEmailCol').value = cfg ? (cfg.email_column || '') : '';
         document.getElementById('cfgNameCol').value = cfg ? (cfg.name_column || '') : '';
         document.getElementById('cfgWhereClause').value = cfg ? (cfg.where_clause || '') : '';
         document.getElementById('cfgWhereValue').value = cfg ? (cfg.where_value || '') : '';
         showModal('modalSyncConfig');
      }

      document.addEventListener('click', (e) => {
         const subBtn = e.target.closest('[data-action="open-subscribers"]');
         if (subBtn) {
            openSubscriberModal(
               subBtn.getAttribute('data-brand-id'),
               subBtn.getAttribute('data-brand-name') || '',
               subBtn.getAttribute('data-brand-slug') || ''
            );
            return;
         }

         const cfgBtn = e.target.closest('[data-action="open-sync-config"]');
         if (cfgBtn) {
            openSyncConfigModal(
               cfgBtn.getAttribute('data-brand-id'),
               cfgBtn.getAttribute('data-brand-name') || '',
               {
                  table_name: cfgBtn.getAttribute('data-table-name') || '',
                  email_column: cfgBtn.getAttribute('data-email-col') || '',
                  name_column: cfgBtn.getAttribute('data-name-col') || '',
                  where_clause: cfgBtn.getAttribute('data-where-clause') || '',
                  where_value: cfgBtn.getAttribute('data-where-value') || ''
               }
            );
            return;
         }

         const syncBtn = e.target.closest('[data-sync-mode]');
         if (syncBtn) {
            triggerRemoteSync(syncBtn.getAttribute('data-sync-mode'));
         }
      });

      document.getElementById('formSyncConfig').addEventListener('submit', async (e) => {
         e.preventDefault();
         const brandId = document.getElementById('configBrandId').value;
         try {
            await csRequest(`{{ url('email-sync') }}/${brandId}/config`, {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });
   </script>
@endpush
