@extends('layouts.app')

@section('title', 'Content Management — ' . $brand->name)
@section('page_title', 'Content Management: ' . $brand->name)

@section('content')
   <h1 class="fs-2 mb-3 fw-semibold">@yield('page_title', 'Workspace')</h1>

   <section aria-label="Daftar Materi EDM">
      <div class="card shadow-sm">
         <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
               <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-arrow-left"></i> Workspace
               </a>
               <a href="{{ route('blast.index', $brand->id) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-send me-1"></i> Ke Halaman Blast
               </a>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddContent">
               <i class="bi bi-plus-lg me-1"></i> Tambah Materi EDM
            </button>
         </div>

         <div class="table-responsive">
            <table class="table table-hover mb-0">
               <thead class="table-light">
                  <tr>
                     <th style="width: 50px;">#</th>
                     <th>Nama Materi</th>
                     <th>Subject Email</th>
                     <th style="width: 120px;">Tanggal</th>
                     <th style="width: 90px;">Jam</th>
                     <th style="width: 90px;">Asset</th>
                     <th class="text-end" style="width: 310px;">Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse ($contents as $index => $item)
                     <tr>
                        <td class="text-body-secondary">{{ $contents->firstItem() + $index }}</td>
                        <td class="fw-medium">{{ $item->materi_name ?: '-' }}</td>
                        <td>{{ $item->subject ?: '-' }}</td>
                        <td><code>{{ $item->formatted_date }}</code></td>
                        <td><code>{{ $item->formatted_time }}</code></td>
                        <td>
                           <span class="badge bg-body-tertiary text-body border">{{ $item->assets_count }} file</span>
                        </td>
                        <td class="text-end">
                           <div class="btn-group btn-group-sm" role="group" aria-label="Aksi Materi">
                              <button type="button" class="btn btn-outline-secondary"
                                 data-action="edit-meta"
                                 data-id="{{ $item->id }}"
                                 data-materi="{{ $item->materi_name }}"
                                 data-subject="{{ $item->subject }}"
                                 title="Ubah Nama Materi & Subject">
                                 <i class="bi bi-pencil"></i> Edit Info
                              </button>
                              <a href="{{ route('contents.editor', $item->id) }}" class="btn btn-outline-primary"
                                 title="Edit HTML & Live Preview">
                                 <i class="bi bi-code-slash me-1"></i> Editor HTML
                              </a>
                              <a href="{{ route('contents.preview', $item->id) }}" target="_blank" rel="noopener"
                                 class="btn btn-outline-secondary" title="Buka Pratinjau di Tab Baru">
                                 <i class="bi bi-box-arrow-up-right"></i> Preview
                              </a>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="7" class="text-center text-body-secondary py-4">
                           Belum ada materi konten EDM untuk brand {{ $brand->name }}.
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         @if ($contents->hasPages())
            <div class="card-footer bg-transparent py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
               <div class="small text-body-secondary">
                  Menampilkan {{ $contents->firstItem() }}–{{ $contents->lastItem() }} dari {{ $contents->total() }} materi
               </div>
               <div class="m-0">
                  {{ $contents->links() }}
               </div>
            </div>
         @endif
      </div>
   </section>

   <!-- Modal Tambah Materi -->
   <div class="modal fade" id="modalAddContent" tabindex="-1" aria-labelledby="modalAddContentLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formAddContent">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalAddContentLabel">Tambah Materi Baru — {{ $brand->name }}</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="addMateriName" class="form-label small fw-semibold">Nama Materi (Internal)</label>
                  <input type="text" id="addMateriName" name="materi_name" class="form-control"
                     placeholder="Contoh: Promo 11.11 Spesial" required>
               </div>
               <div class="mb-3">
                  <label for="addSubject" class="form-label small fw-semibold">Subject Email (Tampil di Inbox
                     Penerima)</label>
                  <input type="text" id="addSubject" name="subject" class="form-control"
                     placeholder="Contoh: SPECIAL DEALS, BETTER COFFEE!" required>
               </div>
               <div class="row g-2">
                  <div class="col-7">
                     <label for="addBlastDate" class="form-label small fw-semibold">Tanggal Folder</label>
                     <input type="date" id="addBlastDate" name="blast_date" class="form-control"
                        value="{{ date('Y-m-d') }}" required>
                  </div>
                  <div class="col-5">
                     <label for="addBlastTime" class="form-label small fw-semibold">Jam Blast</label>
                     <select id="addBlastTime" name="blast_time" class="form-select" required>
                        <option value="11:00">11:00</option>
                        <option value="17:00">17:00</option>
                     </select>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitAdd">
                  <i class="bi bi-check-lg me-1"></i> Simpan Materi
               </button>
            </div>
         </form>
      </div>
   </div>

   <!-- Modal Edit Meta Materi -->
   <div class="modal fade" id="modalEditMeta" tabindex="-1" aria-labelledby="modalEditMetaLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form class="modal-content" id="formEditMeta">
            <input type="hidden" id="editContentId">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalEditMetaLabel">Edit Informasi Materi</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="editMateriName" class="form-label small fw-semibold">Nama Materi</label>
                  <input type="text" id="editMateriName" name="materi_name" class="form-control" required>
               </div>
               <div class="mb-3">
                  <label for="editSubject" class="form-label small fw-semibold">Subject Email</label>
                  <input type="text" id="editSubject" name="subject" class="form-control" required>
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
      document.getElementById('formAddContent').addEventListener('submit', async (e) => {
         e.preventDefault();
         const btnSubmit = document.getElementById('btnSubmitAdd');
         btnSubmit.disabled = true;
         try {
            await csRequest(@json(route('contents.store', $brand->id)), {
               method: 'POST',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            btnSubmit.disabled = false;
            alert(err.message);
         }
      });

      function openEditMeta(id, materi, subject) {
         document.getElementById('editContentId').value = id;
         document.getElementById('editMateriName').value = materi;
         document.getElementById('editSubject').value = subject;
         showModal('modalEditMeta');
      }

      document.getElementById('formEditMeta').addEventListener('submit', async (e) => {
         e.preventDefault();
         const id = document.getElementById('editContentId').value;
         try {
            await csRequest(`{{ url('contents') }}/${id}`, {
               method: 'PUT',
               form: e.currentTarget
            });
            window.location.reload();
         } catch (err) {
            alert(err.message);
         }
      });

      document.addEventListener('click', (e) => {
         const editMetaBtn = e.target.closest('[data-action="edit-meta"]');
         if (editMetaBtn) {
            openEditMeta(
               editMetaBtn.getAttribute('data-id'),
               editMetaBtn.getAttribute('data-materi') || '',
               editMetaBtn.getAttribute('data-subject') || ''
            );
         }
      });
   </script>
@endpush
