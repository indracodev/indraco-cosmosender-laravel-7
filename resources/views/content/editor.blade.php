@extends('layouts.app')

@section('title', 'Editor HTML — ' . $content->materi_name)
@section('page_title', 'Editor HTML EDM: ' . $brand->name)

@push('styles')
   <link rel="stylesheet" href="{{ asset('assets/vendor/codemirror/lib/codemirror.css') }}">
   <link rel="stylesheet" href="{{ asset('assets/vendor/codemirror/theme/dracula.css') }}">
@endpush

@section('content')
   <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div>
         <h1 class="fs-4 mb-0 fw-semibold">{{ $content->materi_name }}</h1>
         <div class="text-body-secondary small">
            Brand: <strong class="text-body">{{ $brand->name }}</strong>
            &middot; Subject: <strong class="text-body">{{ $content->subject }}</strong>
            &middot; Jadwal: <code>{{ $content->formatted_date }} {{ $content->formatted_time }}</code>
         </div>
      </div>

      <div class="d-flex flex-wrap align-items-center gap-2">
         <a href="{{ route('contents.index', $brand->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Materi
         </a>
         <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
            data-bs-target="#modalRedirectList">
            <i class="bi bi-link-45deg me-1"></i> Daftar URL Tracking / Redirect
         </button>
         <a href="{{ route('contents.preview', $content->id) }}" target="_blank" rel="noopener"
            class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-box-arrow-up-right me-1"></i> Preview Tab Baru
         </a>
         <button type="button" class="btn btn-sm btn-outline-primary" id="btnPushLive"
            title="Unggah HTML & Gambar lokal ke mbcontentservices Server Live">
            <i class="bi bi-cloud-upload me-1"></i> Push Materi ke Server Live
         </button>
         <button type="button" class="btn btn-sm btn-primary" id="btnSaveHtml">
            <i class="bi bi-floppy me-1"></i> Simpan HTML
         </button>
      </div>
   </div>

   <div id="editorAlertBox"></div>

   <div class="row g-3">
      <!-- Kolom Kiri: Upload & Daftar Asset Gambar -->
      <div class="col-12 col-lg-4">
         <section class="card p-3 d-flex flex-column shadow-sm" style="height: calc(100vh - 210px); min-height: 500px;"
            aria-label="Manajemen Asset Gambar">
            <h2 class="h6 fw-bold mb-1">Asset Gambar Banner</h2>
            <p class="text-body-secondary small mb-2">Unggah gambar lalu klik tombol salin URL untuk ditempelkan ke tag
               <code>&lt;img src="..."&gt;</code>.
            </p>

            <form id="formUploadAsset" class="mb-3">
               <div class="input-group input-group-sm">
                  <input type="file" id="assetFileInput" name="files[]" class="form-control" accept="image/*" multiple
                     required>
                  <button type="submit" class="btn btn-primary" id="btnUploadAsset">
                     <i class="bi bi-upload"></i> Upload
                  </button>
               </div>
            </form>

            <div class="overflow-y-auto flex-grow-1" id="assetListContainer"></div>
         </section>
      </div>

      <!-- Kolom Tengah: CodeMirror Editor -->
      <div class="col-12 col-lg-8">
         <section class="card p-3 d-flex flex-column shadow-sm" style="height: calc(100vh - 210px); min-height: 500px;"
            aria-label="Editor Kode HTML">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
               <span class="small fw-semibold text-body-secondary">Kode Sumber HTML (Gunakan <code>{email}</code> untuk
                  email dinamis)</span>
               <div class="d-flex align-items-center gap-3">
                  <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-body-secondary"
                     id="btnToggleWrap" title="Pintasan: Alt + Z">
                     <i class="bi bi-text-wrap"></i> <span id="wrapStatusLabel">Word Wrap: OFF</span>
                  </button>
                  <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="btnRefreshPreview">
                     <i class="bi bi-arrow-clockwise"></i> Segarkan Preview
                  </button>
               </div>
            </div>
            <div class="d-flex flex-column flex-grow-1 overflow-hidden" style="min-width: 0;">
               <textarea id="codeMirrorTextarea">{{ $content->body_content }}</textarea>
            </div>
         </section>
      </div>

      <!-- Kolom Kanan: Live Preview Iframe -->
      <div class="col-12">
         <section class="card p-3 d-flex flex-column shadow-sm" style="height: calc(100vh - 210px); min-height: 500px;"
            aria-label="Pratinjau Langsung HTML">
            <div class="d-flex justify-content-between align-items-center mb-2">
               <span class="small fw-semibold text-body-secondary">Live HTML Preview</span>
               <span class="badge bg-body-tertiary text-body border">Otomatis diperbarui</span>
            </div>
            <iframe id="livePreviewFrame" class="w-100 flex-grow-1 border bg-white"
               title="Live Preview Email HTML"></iframe>
         </section>
      </div>
   </div>

   <!-- Modal Redirect URL Helper -->
   <div class="modal fade" id="modalRedirectList" tabindex="-1" aria-labelledby="modalRedirectListLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
         <div class="modal-content">
            <div class="modal-header">
               <h2 class="modal-title h6 fw-bold" id="modalRedirectListLabel">Daftar URL Tracking &amp; Redirect</h2>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
               <p class="text-body-secondary small mb-2">
                  Tag <code>{email}</code> tidak perlu diubah karena akan otomatis diganti dengan email penerima saat
                  proses pengiriman berlangsung.
               </p>
               <div class="table-responsive">
                  <table class="table table-sm mb-0">
                     <thead class="table-light">
                        <tr>
                           <th>Nama Link</th>
                           <th>Template URL</th>
                           <th class="text-end">Salin</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach ($redirects as $r)
                           <tr>
                              <td class="fw-medium">{{ $r['name'] }}</td>
                              <td><code class="small">{{ $r['url'] }}</code></td>
                              <td class="text-end">
                                 <button type="button" class="btn btn-sm btn-outline-secondary py-0"
                                    data-copy-url="{{ $r['url'] }}">
                                    <i class="bi bi-clipboard"></i> Salin
                                 </button>
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection

@push('scripts')
   <script src="{{ asset('assets/vendor/codemirror/lib/codemirror.js') }}"></script>
   <script src="{{ asset('assets/vendor/codemirror/mode/xml/xml.js') }}"></script>
   <script src="{{ asset('assets/vendor/codemirror/addon/edit/matchbrackets.js') }}"></script>
   <script>
      const contentId = {{ (int) $content->id }};
      const initialAssets = @json($assets);
      let cmEditor = null;
      let previewTimer = null;

      function toggleWordWrap() {
         if (!cmEditor) return;
         const nextWrap = !cmEditor.getOption('lineWrapping');
         cmEditor.setOption('lineWrapping', nextWrap);
         const label = document.getElementById('wrapStatusLabel');
         const btn = document.getElementById('btnToggleWrap');
         if (label) {
            label.textContent = nextWrap ? 'Word Wrap: ON' : 'Word Wrap: OFF';
         }
         if (btn) {
            btn.classList.toggle('text-primary', nextWrap);
            btn.classList.toggle('text-body-secondary', !nextWrap);
         }
      }

      document.addEventListener('DOMContentLoaded', () => {
         const textArea = document.getElementById('codeMirrorTextarea');
         cmEditor = CodeMirror.fromTextArea(textArea, {
            lineNumbers: true,
            mode: 'xml',
            htmlMode: true,
            matchBrackets: true,
            theme: 'dracula',
            lineWrapping: false,
            extraKeys: {
               'Alt-Z': () => toggleWordWrap()
            }
         });

         cmEditor.on('change', () => {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(refreshLivePreview, 350);
         });

         renderAssets(initialAssets || []);
         refreshLivePreview();
      });

      function refreshLivePreview() {
         if (!cmEditor) return;
         const rawHtml = cmEditor.getValue();
         const frame = document.getElementById('livePreviewFrame');
         if (!frame) return;
         const doc = frame.contentDocument || frame.contentWindow.document;
         doc.open();
         doc.write(rawHtml ||
            '<div style="font-family:sans-serif;padding:2rem;color:#94a3b8;text-align:center;">Ketik kode HTML di sebelah kiri untuk melihat pratinjau langsung.</div>'
         );
         doc.close();
      }

      function showEditorAlert(msg, type = 'success') {
         document.getElementById('editorAlertBox').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show py-2 px-3 small mb-3" role="alert">
                ${msg}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        `;
      }

      function renderAssets(assets) {
         const container = document.getElementById('assetListContainer');
         if (!assets.length) {
            container.innerHTML =
               '<div class="text-body-secondary small py-3 text-center">Belum ada gambar diunggah pada materi ini.</div>';
            return;
         }

         let html = '<div class="d-flex flex-column gap-2">';
         assets.forEach((item) => {
            html += `
                <div class="border p-2 bg-body-tertiary">
                    <img src="${item.local_url}" alt="${item.name}" class="w-100 bg-secondary-subtle mb-2" style="height: 64px; object-fit: contain;">
                    <div class="small text-truncate fw-medium mb-1" title="${item.name}">${item.name}</div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-0" data-copy-url="${item.local_url}">
                            <i class="bi bi-clipboard"></i> URL Lokal/Aktif
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-0" data-copy-url="${item.live_url}">
                            <i class="bi bi-globe"></i> URL Live
                        </button>
                    </div>
                </div>
            `;
         });
         html += '</div>';
         container.innerHTML = html;
      }

      async function copyText(text, btnEl = null) {
         try {
            if (navigator.clipboard && window.isSecureContext) {
               await navigator.clipboard.writeText(text);
            } else {
               const tempInput = document.createElement('textarea');
               tempInput.value = text;
               tempInput.style.position = 'fixed';
               tempInput.style.opacity = '0';
               document.body.appendChild(tempInput);
               tempInput.focus();
               tempInput.select();
               document.execCommand('copy');
               document.body.removeChild(tempInput);
            }

            if (btnEl) {
               const originalHtml = btnEl.innerHTML;
               btnEl.innerHTML = '<i class="bi bi-check-lg text-success"></i> Tersalin';
               setTimeout(() => {
                  btnEl.innerHTML = originalHtml;
               }, 1500);
            }
            showEditorAlert('URL berhasil disalin ke clipboard: <code>' + text + '</code>', 'info');
         } catch (err) {
            alert('Gagal menyalin URL.');
         }
      }

      document.getElementById('btnToggleWrap').addEventListener('click', toggleWordWrap);
      document.getElementById('btnRefreshPreview').addEventListener('click', refreshLivePreview);

      document.addEventListener('click', (e) => {
         const copyBtn = e.target.closest('[data-copy-url]');
         if (copyBtn) {
            copyText(copyBtn.getAttribute('data-copy-url'), copyBtn);
         }
      });

      document.getElementById('formUploadAsset').addEventListener('submit', async (e) => {
         e.preventDefault();
         const fileInput = document.getElementById('assetFileInput');
         if (!fileInput.files.length) return;

         const formData = new FormData();
         for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('files[]', fileInput.files[i]);
         }

         const btnUpload = document.getElementById('btnUploadAsset');
         btnUpload.disabled = true;

         try {
            const res = await csRequest(`{{ url('contents') }}/${contentId}/assets`, {
               method: 'POST',
               data: formData
            });
            btnUpload.disabled = false;
            fileInput.value = '';
            renderAssets(res.assets || []);
            showEditorAlert(res.message, 'success');
         } catch (err) {
            btnUpload.disabled = false;
            showEditorAlert(err.message, 'danger');
         }
      });

      document.getElementById('btnSaveHtml').addEventListener('click', async (e) => {
         const bodyContent = cmEditor.getValue();
         const btn = e.currentTarget;
         btn.disabled = true;

         try {
            const res = await csRequest(`{{ url('contents') }}/${contentId}/body`, {
               method: 'POST',
               data: {
                  body_content: bodyContent
               }
            });
            btn.disabled = false;
            showEditorAlert(res.message, 'success');
         } catch (err) {
            btn.disabled = false;
            showEditorAlert(err.message, 'danger');
         }
      });

      document.getElementById('btnPushLive').addEventListener('click', async (e) => {
         if (!confirm(
               'Sinkronkan kode HTML dan seluruh gambar lokal materi ini ke Server Live (mbcontentservices) sekarang?'
            )) {
            return;
         }
         const btn = e.currentTarget;
         btn.disabled = true;

         try {
            await csRequest(`{{ url('contents') }}/${contentId}/body`, {
               method: 'POST',
               data: {
                  body_content: cmEditor.getValue()
               }
            });
            const res = await csRequest(`{{ url('contents') }}/${contentId}/push-live`, {
               method: 'POST'
            });
            btn.disabled = false;
            showEditorAlert(res.message, res.data && res.data.remote_synced ? 'success' : 'warning');
         } catch (err) {
            btn.disabled = false;
            showEditorAlert(err.message, 'danger');
         }
      });
   </script>
@endpush

