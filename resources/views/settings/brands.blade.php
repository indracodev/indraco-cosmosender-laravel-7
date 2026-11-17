@extends('layouts.app')

@section('title', 'Brand Configuration')
@section('page_title', 'Brand Configuration')

@section('content')
   <h1 class="fs-2 mb-3 fw-semibold">@yield('page_title', 'Workspace')</h1>

   <section class="card shadow-sm" aria-label="Pengaturan Brand">
      <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
         <div>
            <h2 class="h6 fw-bold mb-0">Daftar Konfigurasi Brand</h2>
            <p class="text-body-secondary small mb-0">Atur parameter domain, kuota pengiriman per batch, dan interval jeda
               jam antar-blasting.</p>
         </div>
         <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddBrand">
            <i class="bi bi-plus-lg me-1"></i> Tambah Brand
         </button>
      </div>

      <div class="table-responsive">
         <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
               <tr>
                  <th style="width: 45px;">#</th>
                  <th>Nama Brand &amp; Alias</th>
                  <th>Domain &amp; Path</th>
                  <th>Content Service</th>
                  <th>Kuota Batch</th>
                  <th>Jeda (Jam)</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($brands as $index => $brand)
                  <tr>
                     <td class="text-body-secondary">{{ $index + 1 }}</td>
                     <td>
                        <div class="fw-semibold">{{ $brand->name }}</div>
                        <div class="text-body-secondary small">{{ $brand->email_alias ?: '-' }}</div>
                     </td>
                     <td class="small">
                        <div>Domain: <code>{{ $brand->domain }}</code></div>
                        <div class="text-body-secondary">Folder: <code>{{ $brand->aseet_namespace }}</code></div>
                     </td>
                     <td>
                        @if ($brand->service)
                           <span class="badge text-dark border"
                              style="background-color: {{ $brand->service->color ?: '#f1f5f9' }};">
                              {{ optional($brand->service->server)->name }} / {{ $brand->service->service }}
                           </span>
                        @else
                           <span class="text-body-secondary">-</span>
                        @endif
                     </td>
                     <td><strong>{{ $brand->blast_limit ?: 0 }}</strong> email</td>
                     <td><strong>{{ $brand->blast_hour_interval ?: 0 }}</strong> jam</td>
                     <td>
                        @if ($brand->flag === 'Y')
                           <span
                              class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">Aktif</span>
                        @else
                           <span class="badge text-bg-secondary">Non-Aktif</span>
                        @endif
                     </td>
                     <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                           onclick='openEditBrandModal(@json($brand))'>
                           <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </section>

   <!-- Modal Tambah Brand -->
   <div class="modal fade" id="modalAddBrand" tabindex="-1" aria-labelledby="modalAddBrandLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
         <form class="modal-content" id="formAddBrand">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalAddBrandLabel">Tambah Brand Baru</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="row g-3">
                  <div class="col-md-6">
                     <label for="addBrandName" class="form-label small fw-semibold">Nama Brand</label>
                     <input type="text" id="addBrandName" name="name" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                     <label for="addEmailAlias" class="form-label small fw-semibold">Display Name Pengirim (Email
                        Alias)</label>
                     <input type="text" id="addEmailAlias" name="email_alias" class="form-control"
                        placeholder="Contoh: Supresso Coffee">
                  </div>
                  <div class="col-md-6">
                     <label for="addDomain" class="form-label small fw-semibold">Domain Folder</label>
                     <input type="text" id="addDomain" name="domain" class="form-control"
                        placeholder="Contoh: supresso.co.id" required>
                  </div>
                  <div class="col-md-6">
                     <label for="addNamespace" class="form-label small fw-semibold">Asset Namespace (Folder
                        Newsletter)</label>
                     <input type="text" id="addNamespace" name="aseet_namespace" class="form-control"
                        value="newsletterv2" required>
                  </div>
                  <div class="col-md-6">
                     <label for="addActualPath" class="form-label small fw-semibold">Actual Path Server</label>
                     <input type="text" id="addActualPath" name="actual_path" class="form-control"
                        placeholder="Contoh: /home/supresso/public_html/" required>
                  </div>
                  <div class="col-md-6">
                     <label for="addContentDomain" class="form-label small fw-semibold">Content Domain URL</label>
                     <input type="text" id="addContentDomain" name="content_domain" class="form-control"
                        placeholder="Contoh: www." required>
                  </div>
                  <div class="col-md-4">
                     <label for="addServiceId" class="form-label small fw-semibold">Content Service</label>
                     <select id="addServiceId" name="service_id" class="form-select" required>
                        @foreach ($services as $s)
                           <option value="{{ $s->id }}">{{ optional($s->server)->name }} / {{ $s->service }}
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-4">
                     <label for="addBlastLimit" class="form-label small fw-semibold">Blast Limit (Email / Batch)</label>
                     <input type="number" id="addBlastLimit" name="blast_limit" class="form-control" value="100"
                        min="0">
                  </div>
                  <div class="col-md-4">
                     <label for="addBlastInterval" class="form-label small fw-semibold">Interval Jeda (Jam)</label>
                     <input type="number" id="addBlastInterval" name="blast_hour_interval" class="form-control"
                        value="1" min="0">
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">
                  <i class="bi bi-check-lg me-1"></i> Simpan Brand
               </button>
            </div>
         </form>
      </div>
   </div>

   <!-- Modal Edit Brand -->
   <div class="modal fade" id="modalEditBrand" tabindex="-1" aria-labelledby="modalEditBrandLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
         <form class="modal-content" id="formEditBrand">
            <input type="hidden" id="editBrandId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalEditBrandLabel">Edit Konfigurasi Brand</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="row g-3">
                  <div class="col-md-6">
                     <label for="editBrandName" class="form-label small fw-semibold">Nama Brand</label>
                     <input type="text" id="editBrandName" name="name" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                     <label for="editEmailAlias" class="form-label small fw-semibold">Display Name Pengirim (Email
                        Alias)</label>
                     <input type="text" id="editEmailAlias" name="email_alias" class="form-control">
                  </div>
                  <div class="col-md-6">
                     <label for="editDomain" class="form-label small fw-semibold">Domain Folder</label>
                     <input type="text" id="editDomain" name="domain" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                     <label for="editNamespace" class="form-label small fw-semibold">Asset Namespace</label>
                     <input type="text" id="editNamespace" name="aseet_namespace" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                     <label for="editActualPath" class="form-label small fw-semibold">Actual Path Server</label>
                     <input type="text" id="editActualPath" name="actual_path" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                     <label for="editContentDomain" class="form-label small fw-semibold">Content Domain URL</label>
                     <input type="text" id="editContentDomain" name="content_domain" class="form-control" required>
                  </div>
                  <div class="col-md-3">
                     <label for="editServiceId" class="form-label small fw-semibold">Content Service</label>
                     <select id="editServiceId" name="service_id" class="form-select" required>
                        @foreach ($services as $s)
                           <option value="{{ $s->id }}">{{ optional($s->server)->name }} / {{ $s->service }}
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-3">
                     <label for="editBlastLimit" class="form-label small fw-semibold">Blast Limit</label>
                     <input type="number" id="editBlastLimit" name="blast_limit" class="form-control" min="0">
                  </div>
                  <div class="col-md-3">
                     <label for="editBlastInterval" class="form-label small fw-semibold">Jeda (Jam)</label>
                     <input type="number" id="editBlastInterval" name="blast_hour_interval" class="form-control"
                        min="0">
                  </div>
                  <div class="col-md-3">
                     <label for="editFlag" class="form-label small fw-semibold">Status Brand</label>
                     <select id="editFlag" name="flag" class="form-select">
                        <option value="Y">Aktif (Y)</option>
                        <option value="N">Non-Aktif (N)</option>
                     </select>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">
                  <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
               </button>
            </div>
         </form>
      </div>
   </div>
@endsection

@push('scripts')
   <script>
      document.getElementById('formAddBrand').addEventListener('submit', async (e) => {
         e.preventDefault();
         try {
            await csRequest(@json(route('settings.brands.store')), {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      function openEditBrandModal(brand) {
         document.getElementById('editBrandId').value = brand.id;
         document.getElementById('editBrandName').value = brand.name || '';
         document.getElementById('editEmailAlias').value = brand.email_alias || '';
         document.getElementById('editDomain').value = brand.domain || '';
         document.getElementById('editNamespace').value = brand.aseet_namespace || '';
         document.getElementById('editActualPath').value = brand.actual_path || '';
         document.getElementById('editContentDomain').value = brand.content_domain || '';
         document.getElementById('editServiceId').value = brand.service_id;
         document.getElementById('editBlastLimit').value = brand.blast_limit ?? 0;
         document.getElementById('editBlastInterval').value = brand.blast_hour_interval ?? 0;
         document.getElementById('editFlag').value = brand.flag;
         showModal('modalEditBrand');
      }

      document.getElementById('formEditBrand').addEventListener('submit', async (e) => {
         e.preventDefault();
         const id = document.getElementById('editBrandId').value;
         try {
            await csRequest(`{{ url('settings/brands') }}/${id}`, {
               method: 'PUT',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });
   </script>
@endpush
