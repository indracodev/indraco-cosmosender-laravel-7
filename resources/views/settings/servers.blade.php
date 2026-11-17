@extends('layouts.app')

@section('title', 'Server Configuration')
@section('page_title', 'Server Configuration')

@section('content')
   <h1 class="fs-2 mb-3 fw-semibold">@yield('page_title', 'Workspace')</h1>

   <section class="card shadow-sm" aria-label="Daftar Server Fisik">
      <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
         <div>
            <h2 class="h6 fw-bold mb-0">Daftar Server Hosting</h2>
            <p class="text-body-secondary small mb-0">Identitas server penampung domain Content Service dan Host SMTP.</p>
         </div>
         <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddServer">
            <i class="bi bi-plus-lg me-1"></i> Tambah Server
         </button>
      </div>

      <div class="table-responsive">
         <table class="table table-hover mb-0">
            <thead class="table-light">
               <tr>
                  <th style="width: 50px;">#</th>
                  <th>Nama Server</th>
                  <th>Domain Root</th>
                  <th>Warna Indikator</th>
                  <th class="text-end">Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($servers as $index => $srv)
                  <tr>
                     <td class="text-body-secondary">{{ $index + 1 }}</td>
                     <td class="fw-semibold">{{ $srv->name }}</td>
                     <td><code>{{ $srv->domain }}</code></td>
                     <td>
                        <span class="badge text-dark border" style="background-color: {{ $srv->color ?: '#f1f5f9' }};">
                           {{ $srv->color ?: '-' }}
                        </span>
                     </td>
                     <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                           data-action="edit-server"
                           data-id="{{ $srv->id }}"
                           data-name="{{ $srv->name }}"
                           data-domain="{{ $srv->domain }}"
                           data-color="{{ $srv->color ?: '#eea177' }}">
                           <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </section>

   <!-- Modal Tambah Server -->
   <div class="modal fade" id="modalAddServer" tabindex="-1" aria-labelledby="modalAddServerLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formAddServer">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalAddServerLabel">Tambah Server Baru</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="addServerName" class="form-label small fw-semibold">Nama Server</label>
                  <input type="text" id="addServerName" name="name" class="form-control"
                     placeholder="Contoh: Solindo" required>
               </div>
               <div class="mb-3">
                  <label for="addServerDomain" class="form-label small fw-semibold">Domain Server</label>
                  <input type="text" id="addServerDomain" name="domain" class="form-control"
                     placeholder="cosmosender.supresso.co.id/" required>
               </div>
               <div class="mb-3">
                  <label for="addServerColor" class="form-label small fw-semibold">Warna Badge</label>
                  <input type="color" id="addServerColor" name="color" class="form-control form-control-color w-100"
                     value="#eea177">
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">Simpan Server</button>
            </div>
         </form>
      </div>
   </div>

   <!-- Modal Edit Server -->
   <div class="modal fade" id="modalEditServer" tabindex="-1" aria-labelledby="modalEditServerLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formEditServer">
            <input type="hidden" id="editServerId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalEditServerLabel">Edit Server</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="editServerName" class="form-label small fw-semibold">Nama Server</label>
                  <input type="text" id="editServerName" name="name" class="form-control" required>
               </div>
               <div class="mb-3">
                  <label for="editServerDomain" class="form-label small fw-semibold">Domain Server</label>
                  <input type="text" id="editServerDomain" name="domain" class="form-control" required>
               </div>
               <div class="mb-3">
                  <label for="editServerColor" class="form-label small fw-semibold">Warna Badge</label>
                  <input type="color" id="editServerColor" name="color"
                     class="form-control form-control-color w-100">
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
            </div>
         </form>
      </div>
   </div>
@endsection

@push('scripts')
   <script>
      document.getElementById('formAddServer').addEventListener('submit', async (e) => {
         e.preventDefault();
         try {
            await csRequest(@json(route('settings.servers.store')), {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      function openEditServer(id, name, domain, color) {
         document.getElementById('editServerId').value = id;
         document.getElementById('editServerName').value = name;
         document.getElementById('editServerDomain').value = domain;
         document.getElementById('editServerColor').value = color || '#eea177';
         showModal('modalEditServer');
      }

      document.addEventListener('click', (e) => {
         const editBtn = e.target.closest('[data-action="edit-server"]');
         if (editBtn) {
            openEditServer(
               editBtn.getAttribute('data-id'),
               editBtn.getAttribute('data-name') || '',
               editBtn.getAttribute('data-domain') || '',
               editBtn.getAttribute('data-color') || '#eea177'
            );
         }
      });

      document.getElementById('formEditServer').addEventListener('submit', async (e) => {
         e.preventDefault();
         const id = document.getElementById('editServerId').value;
         try {
            await csRequest(`{{ url('settings/servers') }}/${id}`, {
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
