@extends('layouts.app')

@section('title', 'Email Relay (SMTP)')
@section('page_title', 'Email Relay (SMTP Pool)')

@section('content')
   <h1 class="fs-2 mb-3 fw-semibold">@yield('page_title', 'Workspace')</h1>

   <section class="card shadow-sm" aria-label="Daftar Akun SMTP Relay">
      <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
         <div>
            <h2 class="h6 fw-bold mb-0">Manajemen Akun SMTP Relay ({{ $relays->total() }} Akun)</h2>
            <p class="text-body-secondary small mb-0">Akun pengirim yang digunakan secara bergantian (Round-Robin) saat
               eksekusi email blast.</p>
         </div>
         <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRelay">
            <i class="bi bi-plus-lg me-1"></i> Tambah Akun Relay
         </button>
      </div>

      <div class="table-responsive">
         <table class="table table-hover mb-0">
            <thead class="table-light">
               <tr>
                  <th style="width: 45px;">#</th>
                  <th>Email Username (SMTP)</th>
                  <th>Header From &amp; Display Name</th>
                  <th>Host SMTP</th>
                  <th>Port</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($relays as $index => $r)
                  <tr>
                     <td class="text-body-secondary">{{ $relays->firstItem() + $index }}</td>
                     <td class="fw-semibold">{{ $r->email }}</td>
                     <td>
                        <div><code>{{ $r->email_from ?: '-' }}</code></div>
                        <div class="text-body-secondary small">{{ $r->email_alias ?: '-' }}</div>
                     </td>
                     <td>
                        @if ($r->emailHost)
                           <span class="badge text-dark border"
                              style="background-color: {{ optional($r->emailHost->server)->color ?: '#f1f5f9' }};">
                              {{ optional($r->emailHost->server)->name }} / {{ $r->emailHost->host }}
                           </span>
                        @else
                           <span class="text-body-secondary">-</span>
                        @endif
                     </td>
                     <td><code>{{ $r->port }}</code></td>
                     <td>
                        @if ($r->flag === 'Y')
                           <span
                              class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">Aktif</span>
                        @else
                           <span class="badge text-bg-secondary">Non-Aktif</span>
                        @endif
                     </td>
                     <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                           data-action="edit-relay"
                           data-id="{{ $r->id }}"
                           data-host-id="{{ $r->host_id }}"
                           data-email="{{ $r->email }}"
                           data-port="{{ $r->port ?: 465 }}"
                           data-email-from="{{ $r->email_from }}"
                           data-email-alias="{{ $r->email_alias }}"
                           data-flag="{{ $r->flag }}">
                           <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>

      @if ($relays->hasPages())
         <div class="card-footer bg-transparent py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="small text-body-secondary">
               Menampilkan {{ $relays->firstItem() }}–{{ $relays->lastItem() }} dari {{ $relays->total() }} akun relay
            </div>
            <div class="m-0">
               {{ $relays->links() }}
            </div>
         </div>
      @endif
   </section>

   <!-- Modal Tambah Relay -->
   <div class="modal fade" id="modalAddRelay" tabindex="-1" aria-labelledby="modalAddRelayLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formAddRelay">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalAddRelayLabel">Tambah Akun SMTP Relay</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="addRelayHost" class="form-label small fw-semibold">Host SMTP</label>
                  <select id="addRelayHost" name="host_id" class="form-select" required>
                     @foreach ($hosts as $h)
                        <option value="{{ $h->id }}">{{ optional($h->server)->name }} / {{ $h->host }}
                        </option>
                     @endforeach
                  </select>
               </div>
               <div class="mb-3">
                  <label for="addRelayEmail" class="form-label small fw-semibold">Username Email SMTP</label>
                  <input type="email" id="addRelayEmail" name="email" class="form-control"
                     placeholder="no-reply01@indracostore.com" required>
               </div>
               <div class="row g-2 mb-3">
                  <div class="col-8">
                     <label for="addRelayPass" class="form-label small fw-semibold">Password SMTP</label>
                     <x-input-password id="addRelayPass" name="password" :required="true" />
                  </div>
                  <div class="col-4">
                     <label for="addRelayPort" class="form-label small fw-semibold">Port SSL</label>
                     <input type="number" id="addRelayPort" name="port" class="form-control" value="465" required>
                  </div>
               </div>
               <div class="mb-3">
                  <label for="addRelayFrom" class="form-label small fw-semibold">Header Email From</label>
                  <input type="email" id="addRelayFrom" name="email_from" class="form-control"
                     placeholder="no-reply@indracostore.com" required>
               </div>
               <div class="mb-3">
                  <label for="addRelayAlias" class="form-label small fw-semibold">Display Name Default (Alias)</label>
                  <input type="text" id="addRelayAlias" name="email_alias" class="form-control"
                     placeholder="Indraco Store">
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">Simpan Relay</button>
            </div>
         </form>
      </div>
   </div>

   <!-- Modal Edit Relay -->
   <div class="modal fade" id="modalEditRelay" tabindex="-1" aria-labelledby="modalEditRelayLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formEditRelay">
            <input type="hidden" id="editRelayId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalEditRelayLabel">Edit Akun SMTP Relay</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="editRelayHost" class="form-label small fw-semibold">Host SMTP</label>
                  <select id="editRelayHost" name="host_id" class="form-select" required>
                     @foreach ($hosts as $h)
                        <option value="{{ $h->id }}">{{ optional($h->server)->name }} / {{ $h->host }}
                        </option>
                     @endforeach
                  </select>
               </div>
               <div class="mb-3">
                  <label for="editRelayEmail" class="form-label small fw-semibold">Username Email SMTP</label>
                  <input type="email" id="editRelayEmail" name="email" class="form-control" required>
               </div>
               <div class="row g-2 mb-3">
                  <div class="col-8">
                     <label for="editRelayPass" class="form-label small fw-semibold">Password Baru (Kosongkan jika
                        tetap)</label>
                     <x-input-password id="editRelayPass" name="password" placeholder="••••••••" />
                  </div>
                  <div class="col-4">
                     <label for="editRelayPort" class="form-label small fw-semibold">Port SSL</label>
                     <input type="number" id="editRelayPort" name="port" class="form-control" required>
                  </div>
               </div>
               <div class="mb-3">
                  <label for="editRelayFrom" class="form-label small fw-semibold">Header Email From</label>
                  <input type="email" id="editRelayFrom" name="email_from" class="form-control" required>
               </div>
               <div class="row g-2">
                  <div class="col-8">
                     <label for="editRelayAlias" class="form-label small fw-semibold">Display Name Default
                        (Alias)</label>
                     <input type="text" id="editRelayAlias" name="email_alias" class="form-control">
                  </div>
                  <div class="col-4">
                     <label for="editRelayFlag" class="form-label small fw-semibold">Status</label>
                     <select id="editRelayFlag" name="flag" class="form-select">
                        <option value="Y">Aktif (Y)</option>
                        <option value="N">Non-Aktif (N)</option>
                     </select>
                  </div>
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
      document.getElementById('formAddRelay').addEventListener('submit', async (e) => {
         e.preventDefault();
         try {
            await csRequest(@json(route('settings.relays.store')), {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      function openEditRelayModal(relay) {
         document.getElementById('editRelayId').value = relay.id;
         document.getElementById('editRelayHost').value = relay.host_id;
         document.getElementById('editRelayEmail').value = relay.email;
         document.getElementById('editRelayPass').value = '';
         document.getElementById('editRelayPort').value = relay.port || 465;
         document.getElementById('editRelayFrom').value = relay.email_from || '';
         document.getElementById('editRelayAlias').value = relay.email_alias || '';
         document.getElementById('editRelayFlag').value = relay.flag;
         showModal('modalEditRelay');
      }

      document.addEventListener('click', (e) => {
         const editBtn = e.target.closest('[data-action="edit-relay"]');
         if (editBtn) {
            openEditRelayModal({
               id: editBtn.getAttribute('data-id'),
               host_id: editBtn.getAttribute('data-host-id'),
               email: editBtn.getAttribute('data-email') || '',
               port: editBtn.getAttribute('data-port') || 465,
               email_from: editBtn.getAttribute('data-email-from') || '',
               email_alias: editBtn.getAttribute('data-email-alias') || '',
               flag: editBtn.getAttribute('data-flag') || 'Y'
            });
         }
      });

      document.getElementById('formEditRelay').addEventListener('submit', async (e) => {
         e.preventDefault();
         const id = document.getElementById('editRelayId').value;
         try {
            await csRequest(`{{ url('settings/relays') }}/${id}`, {
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
