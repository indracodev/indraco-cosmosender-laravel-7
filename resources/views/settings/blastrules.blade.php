@extends('layouts.app')

@section('title', 'Blast Rules & Test Recipients')
@section('page_title', 'Blast Rules & Test Configuration')

@section('content')
   <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
      <div>
         <h1 class="fs-2 mb-0 fw-semibold">@yield('page_title', 'Workspace')</h1>
         <p class="text-body-secondary small mb-0">
            Kelola kelompok akun pengirim (SMTP Relay) dan daftar email uji coba (Test Recipients) untuk setiap Brand.
         </p>
      </div>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRule">
         <i class="bi bi-plus-lg me-1"></i> Tambah Rule Baru
      </button>
   </div>

   <div class="d-flex flex-column gap-3">
      @foreach ($brands as $brand)
         <section class="card shadow-sm" aria-label="Aturan Blast {{ $brand->name }}">
            <div
               class="card-header bg-body-tertiary py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
               <div class="d-flex align-items-center gap-2">
                  <h2 class="h6 fw-bold mb-0">{{ $brand->name }}</h2>
                  <span class="badge text-bg-secondary">{{ $brand->activeBlastRules->count() }} Rule</span>
               </div>
               <a href="{{ route('blast.index', $brand->id) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-send me-1"></i> Buka Halaman Blast
               </a>
            </div>

            <div class="card-body p-3">
               <div class="row g-3">
                  @forelse ($brand->activeBlastRules as $rule)
                     <div class="col-12 col-xl-6">
                        <div class="border p-3 h-100 d-flex flex-column justify-content-between">
                           <div>
                              <div class="d-flex gap-2 align-items-center justify-content-between mb-2">
                                 <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span
                                       class="badge {{ $rule->type === 'blast' ? 'text-bg-danger' : 'text-bg-secondary' }}">
                                       {{ strtoupper($rule->type) }}
                                    </span>
                                    <h3 class="h6 fw-bold mb-0">{{ $rule->name }}</h3>
                                 </div>
                                 <div class="d-flex flex-wrap align-items-center justify-content-end gap-1">
                                    <span class="badge text-dark border"
                                       style="background-color: {{ optional(optional($rule->emailHost)->server)->color ?: '#f1f5f9' }};">
                                       {{ optional(optional($rule->emailHost)->server)->name }} /
                                       {{ optional($rule->emailHost)->host }}
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"
                                       data-action="edit-rule"
                                       data-rule-id="{{ $rule->id }}"
                                       data-rule-name="{{ $rule->name }}"
                                       data-host-id="{{ (int) $rule->host_id }}"
                                       title="Edit Nama / Host Rule">
                                       <i class="bi bi-pencil"></i>
                                    </button>
                                 </div>
                              </div>

                              <!-- Daftar SMTP Relay pada Rule ini -->
                              <div class="mb-3">
                                 <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-semibold text-body-secondary">Akun Pengirim / SMTP Relays
                                       ({{ $rule->activeDetails->count() }})
                                    </span>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none"
                                       data-action="add-rule-relay"
                                       data-rule-id="{{ $rule->id }}"
                                       data-rule-name="{{ $rule->name }}"
                                       data-host-id="{{ (int) $rule->host_id }}">
                                       <i class="bi bi-plus-circle"></i> Tambah Relay
                                    </button>
                                 </div>
                                 <div class="d-flex flex-wrap gap-1">
                                    @forelse ($rule->activeDetails as $detail)
                                       @if ($detail->relay)
                                          <span
                                             class="badge bg-body-tertiary text-body border d-inline-flex align-items-center gap-1 py-1 px-2">
                                             <span>{{ $detail->relay->email }}</span>
                                             <button type="button" class="btn-close" style="font-size: 0.55rem;"
                                                aria-label="Hapus Relay"
                                                data-action="remove-rule-relay"
                                                data-detail-id="{{ $detail->id }}"
                                                data-email="{{ $detail->relay->email }}"></button>
                                          </span>
                                       @endif
                                    @empty
                                       <span class="text-body-secondary small fst-italic">Belum ada SMTP Relay</span>
                                    @endforelse
                                 </div>
                              </div>

                              <!-- Daftar Penerima Uji Coba (Khusus Rule Tipe Test) -->
                              @if ($rule->type === 'test')
                                 <div class="border-top pt-2 mt-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                       <span class="small fw-semibold text-body-secondary">Email Penerima Send Test
                                          ({{ $rule->activeRecipients->count() }})</span>
                                       <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none"
                                          data-action="add-rule-recipient"
                                          data-rule-id="{{ $rule->id }}"
                                          data-rule-name="{{ $rule->name }}">
                                          <i class="bi bi-person-plus"></i> Tambah Email Tester
                                       </button>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                       @forelse ($rule->activeRecipients as $recip)
                                          <span
                                             class="badge text-bg-info-subtle text-info-emphasis border border-info-subtle d-inline-flex align-items-center gap-1 py-1 px-2">
                                             <span>{{ $recip->email }} ({{ $recip->name }})</span>
                                             <button type="button" class="btn-close" style="font-size: 0.55rem;"
                                                aria-label="Hapus Penerima"
                                                data-action="remove-rule-recipient"
                                                data-recipient-id="{{ $recip->id }}"
                                                data-email="{{ $recip->email }}"></button>
                                          </span>
                                       @empty
                                          <span class="text-body-secondary small fst-italic">Belum ada email tester</span>
                                       @endforelse
                                    </div>
                                 </div>
                              @endif
                           </div>
                        </div>
                     </div>
                  @empty
                     <div class="col-12 text-body-secondary small">Belum ada Blast Rule untuk brand ini.</div>
                  @endforelse
               </div>
            </div>
         </section>
      @endforeach
   </div>

   <!-- Modal Tambah Rule Baru -->
   <div class="modal fade" id="modalAddRule" tabindex="-1" aria-labelledby="modalAddRuleLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formAddRule">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalAddRuleLabel">Tambah Blast Rule Baru</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="addRuleBrand" class="form-label small fw-semibold">Pilih Brand</label>
                  <select id="addRuleBrand" name="brand_id" class="form-select" required>
                     @foreach ($brands as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                     @endforeach
                  </select>
               </div>
               <div class="mb-3">
                  <label for="addRuleName" class="form-label small fw-semibold">Nama Rule</label>
                  <input type="text" id="addRuleName" name="name" class="form-control"
                     placeholder="Contoh: Send Test Saya" required>
               </div>
               <div class="mb-3">
                  <label for="addRuleType" class="form-label small fw-semibold">Tipe Rule</label>
                  <select id="addRuleType" name="type" class="form-select" required>
                     <option value="test">Test (Kirim ke Daftar Email Tester / Internal)</option>
                     <option value="blast">Blast (Kirim Massal ke Pelanggan Subscriber)</option>
                  </select>
               </div>
               <div class="mb-3">
                  <label for="addRuleHost" class="form-label small fw-semibold">Host SMTP</label>
                  <select id="addRuleHost" name="host_id" class="form-select" required>
                     @foreach ($hosts as $h)
                        <option value="{{ $h->id }}">{{ optional($h->server)->name }} / {{ $h->host }}
                        </option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">
                  <i class="bi bi-check-lg me-1"></i> Simpan Rule
               </button>
            </div>
         </form>
      </div>
   </div>

   <!-- Modal Edit Rule -->
   <div class="modal fade" id="modalEditRule" tabindex="-1" aria-labelledby="modalEditRuleLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formEditRule">
            <input type="hidden" id="editRuleId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalEditRuleLabel">Edit Blast Rule</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="editRuleName" class="form-label small fw-semibold">Nama Rule</label>
                  <input type="text" id="editRuleName" name="name" class="form-control" required>
               </div>
               <div class="mb-3">
                  <label for="editRuleHost" class="form-label small fw-semibold">Host SMTP</label>
                  <select id="editRuleHost" name="host_id" class="form-select" required>
                     @foreach ($hosts as $h)
                        <option value="{{ $h->id }}">{{ optional($h->server)->name }} / {{ $h->host }}
                        </option>
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

   <!-- Modal Tambah Relay ke Rule -->
   <div class="modal fade" id="modalAddRelayToRule" tabindex="-1" aria-labelledby="modalAddRelayToRuleLabel"
      aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formAddRelayToRule">
            <input type="hidden" id="relayRuleId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalAddRelayToRuleLabel">Tambah SMTP Relay ke Rule</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <label for="selectRelayForRule" class="form-label small fw-semibold">Pilih Akun SMTP Relay</label>
               <select id="selectRelayForRule" name="relay_id" class="form-select" required></select>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">Tambahkan Relay</button>
            </div>
         </form>
      </div>
   </div>

   <!-- Modal Tambah Penerima Uji Coba (Test Recipient) -->
   <div class="modal fade" id="modalAddRecipient" tabindex="-1" aria-labelledby="modalAddRecipientLabel"
      aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formAddRecipient">
            <input type="hidden" id="recipientRuleId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalAddRecipientLabel">Tambah Email Tester (Send Test)</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="recipName" class="form-label small fw-semibold">Nama Penerima</label>
                  <input type="text" id="recipName" name="name" class="form-control"
                     placeholder="Contoh: Pradhoko QA" required>
               </div>
               <div class="mb-3">
                  <label for="recipEmail" class="form-label small fw-semibold">Alamat Email Tujuan</label>
                  <input type="email" id="recipEmail" name="email" class="form-control"
                     placeholder="nama@domain.com" required>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary">Simpan Email Tester</button>
            </div>
         </form>
      </div>
   </div>
@endsection

@push('scripts')
   <script>
      const hostsRelaysMap = @json($hosts->keyBy('id'));

      document.getElementById('formAddRule').addEventListener('submit', async (e) => {
         e.preventDefault();
         try {
            await csRequest(@json(route('settings.blastrules.store')), {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      function openEditRuleModal(ruleId, name, hostId) {
         document.getElementById('editRuleId').value = ruleId;
         document.getElementById('editRuleName').value = name;
         document.getElementById('editRuleHost').value = hostId;
         showModal('modalEditRule');
      }

      document.getElementById('formEditRule').addEventListener('submit', async (e) => {
         e.preventDefault();
         const ruleId = document.getElementById('editRuleId').value;
         try {
            await csRequest(`{{ url('settings/blast-rules') }}/${ruleId}`, {
               method: 'PUT',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      function openAddRelayModal(ruleId, ruleName, hostId) {
         document.getElementById('relayRuleId').value = ruleId;
         document.getElementById('modalAddRelayToRuleLabel').textContent = 'Tambah Relay — ' + ruleName;
         const hostObj = hostsRelaysMap[hostId];
         const relays = hostObj && hostObj.relays ? hostObj.relays : [];

         let options = '';
         relays.forEach((r) => {
            options += `<option value="${r.id}">${r.email} (From: ${r.email_from || r.email})</option>`;
         });

         if (!options) {
            options = '<option value="">Tidak ada relay pada host ini</option>';
         }

         document.getElementById('selectRelayForRule').innerHTML = options;
         showModal('modalAddRelayToRule');
      }

      document.getElementById('formAddRelayToRule').addEventListener('submit', async (e) => {
         e.preventDefault();
         const ruleId = document.getElementById('relayRuleId').value;
         try {
            await csRequest(`{{ url('settings/blast-rules') }}/${ruleId}/relays`, {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      async function removeRuleRelay(detailId, email) {
         if (!confirm(`Keluarkan akun SMTP "${email}" dari rule ini?`)) return;
         try {
            await csRequest(`{{ url('settings/blast-rules/relays') }}/${detailId}`, {
               method: 'DELETE'
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      }

      function openAddRecipientModal(ruleId, ruleName) {
         document.getElementById('recipientRuleId').value = ruleId;
         document.getElementById('modalAddRecipientLabel').textContent = 'Tambah Email Tester — ' + ruleName;
         document.getElementById('recipName').value = '';
         document.getElementById('recipEmail').value = '';
         showModal('modalAddRecipient');
      }

      document.getElementById('formAddRecipient').addEventListener('submit', async (e) => {
         e.preventDefault();
         const ruleId = document.getElementById('recipientRuleId').value;
         try {
            await csRequest(`{{ url('settings/blast-rules') }}/${ruleId}/recipients`, {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      async function removeRuleRecipient(recipientId, email) {
         if (!confirm(`Hapus "${email}" dari daftar penerima Send Test?`)) return;
         try {
            await csRequest(`{{ url('settings/blast-rules/recipients') }}/${recipientId}`, {
               method: 'DELETE'
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      }
      document.addEventListener('click', (e) => {
         const editRuleBtn = e.target.closest('[data-action="edit-rule"]');
         if (editRuleBtn) {
            openEditRuleModal(
               editRuleBtn.getAttribute('data-rule-id'),
               editRuleBtn.getAttribute('data-rule-name') || '',
               Number(editRuleBtn.getAttribute('data-host-id') || 0)
            );
            return;
         }

         const addRelayBtn = e.target.closest('[data-action="add-rule-relay"]');
         if (addRelayBtn) {
            openAddRelayModal(
               addRelayBtn.getAttribute('data-rule-id'),
               addRelayBtn.getAttribute('data-rule-name') || '',
               Number(addRelayBtn.getAttribute('data-host-id') || 0)
            );
            return;
         }

         const removeRelayBtn = e.target.closest('[data-action="remove-rule-relay"]');
         if (removeRelayBtn) {
            removeRuleRelay(
               removeRelayBtn.getAttribute('data-detail-id'),
               removeRelayBtn.getAttribute('data-email') || ''
            );
            return;
         }

         const addRecipBtn = e.target.closest('[data-action="add-rule-recipient"]');
         if (addRecipBtn) {
            openAddRecipientModal(
               addRecipBtn.getAttribute('data-rule-id'),
               addRecipBtn.getAttribute('data-rule-name') || ''
            );
            return;
         }

         const removeRecipBtn = e.target.closest('[data-action="remove-rule-recipient"]');
         if (removeRecipBtn) {
            removeRuleRecipient(
               removeRecipBtn.getAttribute('data-recipient-id'),
               removeRecipBtn.getAttribute('data-email') || ''
            );
         }
      });
   </script>
@endpush
