@extends('layouts.app')

@section('title', 'SMTP Host')
@section('page_title', 'SMTP Host Configuration')

@section('content')
   <h1 class="fs-2 mb-3 fw-semibold">@yield('page_title', 'Workspace')</h1>

   <section class="card shadow-sm" aria-label="Daftar Host SMTP">
      <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
         <div>
            <h2 class="h6 fw-bold mb-0">Daftar Domain Host SMTP</h2>
            <p class="text-body-secondary small mb-0">Server surat keluar (Outgoing Mail Server) tempat akun SMTP Relay
               bernaung.</p>
         </div>
         <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddHost">
            <i class="bi bi-plus-lg me-1"></i> Tambah Host SMTP
         </button>
      </div>

      <div class="table-responsive">
         <table class="table table-hover mb-0">
            <thead class="table-light">
               <tr>
                  <th style="width: 50px;">#</th>
                  <th>Hostname SMTP</th>
                  <th>Server Fisik</th>
                  <th class="text-end">Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($hosts as $index => $h)
                  <tr>
                     <td class="text-body-secondary">{{ $index + 1 }}</td>
                     <td><code>{{ $h->host }}</code></td>
                     <td>
                        @if ($h->server)
                           <span class="badge text-dark border"
                              style="background-color: {{ $h->server->color ?: '#f1f5f9' }};">
                              {{ $h->server->name }} ({{ $h->server->domain }})
                           </span>
                        @else
                           <span class="text-body-secondary">-</span>
                        @endif
                     </td>
                     <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                           data-action="edit-host"
                           data-id="{{ $h->id }}"
                           data-host="{{ $h->host }}"
                           data-server-id="{{ (int) $h->server_id }}">
                           <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </section>

   <!-- Modal Tambah Host -->
   <div class="modal fade" id="modalAddHost" tabindex="-1" aria-labelledby="modalAddHostLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formAddHost">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalAddHostLabel">Tambah Host SMTP</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="addHostName" class="form-label small fw-semibold">Hostname SMTP</label>
                  <input type="text" id="addHostName" name="host" class="form-control"
                     placeholder="mail.supresso.com" required>
               </div>
               <div class="mb-3">
                  <label for="addHostServer" class="form-label small fw-semibold">Server Penampung</label>
                  <select id="addHostServer" name="server_id" class="form-select" required>
                     @foreach ($servers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->domain }})</option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">Simpan Host</button>
            </div>
         </form>
      </div>
   </div>

   <!-- Modal Edit Host -->
   <div class="modal fade" id="modalEditHost" tabindex="-1" aria-labelledby="modalEditHostLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formEditHost">
            <input type="hidden" id="editHostId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalEditHostLabel">Edit Host SMTP</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="editHostName" class="form-label small fw-semibold">Hostname SMTP</label>
                  <input type="text" id="editHostName" name="host" class="form-control" required>
               </div>
               <div class="mb-3">
                  <label for="editHostServer" class="form-label small fw-semibold">Server Penampung</label>
                  <select id="editHostServer" name="server_id" class="form-select" required>
                     @foreach ($servers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->domain }})</option>
                     @endforeach
                  </select>
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
      document.getElementById('formAddHost').addEventListener('submit', async (e) => {
         e.preventDefault();
         try {
            await csRequest(@json(route('settings.hosts.store')), {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      function openEditHost(id, host, serverId) {
         document.getElementById('editHostId').value = id;
         document.getElementById('editHostName').value = host;
         document.getElementById('editHostServer').value = serverId;
         showModal('modalEditHost');
      }

      document.addEventListener('click', (e) => {
         const editBtn = e.target.closest('[data-action="edit-host"]');
         if (editBtn) {
            openEditHost(
               editBtn.getAttribute('data-id'),
               editBtn.getAttribute('data-host') || '',
               Number(editBtn.getAttribute('data-server-id') || 0)
            );
         }
      });

      document.getElementById('formEditHost').addEventListener('submit', async (e) => {
         e.preventDefault();
         const id = document.getElementById('editHostId').value;
         try {
            await csRequest(`{{ url('settings/hosts') }}/${id}`, {
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
