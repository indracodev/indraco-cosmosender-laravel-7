@extends('layouts.app')

@section('title', 'Content Management — ' . $brand->name)
@section('page_title', 'Content Management: ' . $brand->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/codemirror/lib/codemirror.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/codemirror/theme/dracula.css') }}">
@endpush

@section('content')
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
            <table class="table table-hover align-middle mb-0">
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
                            <td class="text-body-secondary">{{ $index + 1 }}</td>
                            <td class="fw-medium">{{ $item->materi_name ?: '-' }}</td>
                            <td>{{ $item->subject ?: '-' }}</td>
                            <td><code>{{ $item->formatted_date }}</code></td>
                            <td><code>{{ $item->formatted_time }}</code></td>
                            <td>
                                <span class="badge bg-body-tertiary text-body border">{{ $item->assets_count }} file</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Aksi Materi">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary"
                                        onclick="openHtmlEditor({{ $item->id }})"
                                        title="Edit HTML & Live Preview"
                                    >
                                        <i class="bi bi-code-slash me-1"></i> Editor HTML
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="openEditMeta({{ $item->id }}, @json($item->materi_name), @json($item->subject))"
                                        title="Ubah Nama Materi & Subject"
                                    >
                                        <i class="bi bi-pencil"></i> Edit Info
                                    </button>
                                    <a
                                        href="{{ route('contents.preview', $item->id) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="btn btn-outline-secondary"
                                        title="Buka Pratinjau di Tab Baru"
                                    >
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
                    <input type="text" id="addMateriName" name="materi_name" class="form-control" placeholder="Contoh: Promo 11.11 Spesial" required>
                </div>
                <div class="mb-3">
                    <label for="addSubject" class="form-label small fw-semibold">Subject Email (Tampil di Inbox Penerima)</label>
                    <input type="text" id="addSubject" name="subject" class="form-control" placeholder="Contoh: SPECIAL DEALS, BETTER COFFEE!" required>
                </div>
                <div class="row g-2">
                    <div class="col-7">
                        <label for="addBlastDate" class="form-label small fw-semibold">Tanggal Folder</label>
                        <input type="date" id="addBlastDate" name="blast_date" class="form-control" value="{{ date('Y-m-d') }}" required>
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

<!-- Modal Fullscreen HTML Editor + Live Preview + Asset Manager -->
<div class="modal fade" id="modalHtmlEditor" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalHtmlEditorLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header py-2 px-3 bg-body-tertiary">
                <div>
                    <h2 class="modal-title h6 fw-bold mb-0" id="modalHtmlEditorLabel">Editor HTML EDM</h2>
                    <div class="text-body-secondary" style="font-size: 0.75rem;" id="editorMetaSubtitle"></div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalRedirectList">
                        <i class="bi bi-link-45deg me-1"></i> Daftar URL Tracking / Redirect
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnPushLive" title="Unggah HTML & Gambar lokal ke mbcontentservices Server Live">
                        <i class="bi bi-cloud-upload me-1"></i> Push Materi ke Server Live
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnSaveHtml">
                        <i class="bi bi-floppy me-1"></i> Simpan HTML
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="btnCloseEditor">
                        <i class="bi bi-x-lg"></i> Tutup
                    </button>
                </div>
            </div>

            <div class="modal-body p-3">
                <div id="editorAlertBox"></div>
                <input type="hidden" id="activeEditorContentId">

                <div class="row g-3 h-100">
                    <!-- Kolom Kiri: Upload & Daftar Asset Gambar -->
                    <div class="col-12 col-lg-3">
                        <div class="card p-3 h-100 d-flex flex-column">
                            <h3 class="h6 fw-bold mb-2">Asset Gambar Banner</h3>
                            <p class="text-body-secondary small mb-2">Unggah gambar lalu klik tombol salin URL untuk ditempelkan ke tag <code>&lt;img src="..."&gt;</code>.</p>

                            <form id="formUploadAsset" class="mb-3">
                                <div class="input-group input-group-sm">
                                    <input type="file" id="assetFileInput" name="files[]" class="form-control" accept="image/*" multiple required>
                                    <button type="submit" class="btn btn-primary" id="btnUploadAsset">
                                        <i class="bi bi-upload"></i> Upload
                                    </button>
                                </div>
                            </form>

                            <div class="overflow-y-auto flex-grow-1" style="max-height: calc(100vh - 280px);" id="assetListContainer"></div>
                        </div>
                    </div>

                    <!-- Kolom Tengah: CodeMirror Editor -->
                    <div class="col-12 col-lg-5">
                        <div class="d-flex flex-column" style="height: calc(100vh - 175px); min-height: 460px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-body-secondary">Kode Sumber HTML (Gunakan <code>{email}</code> untuk email dinamis)</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="refreshLivePreview()">
                                    <i class="bi bi-arrow-clockwise"></i> Segarkan Preview
                                </button>
                            </div>
                            <textarea id="codeMirrorTextarea"></textarea>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Live Preview Iframe -->
                    <div class="col-12 col-lg-4">
                        <div class="d-flex flex-column" style="height: calc(100vh - 175px); min-height: 460px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-body-secondary">Live HTML Preview</span>
                                <span class="badge bg-body-tertiary text-body border">Otomatis diperbarui</span>
                            </div>
                            <iframe id="livePreviewFrame" class="w-100 flex-grow-1 border rounded bg-white" title="Live Preview Email HTML"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Redirect URL Helper -->
<div class="modal fade" id="modalRedirectList" tabindex="-1" aria-labelledby="modalRedirectListLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h6 fw-bold" id="modalRedirectListLabel">Daftar URL Tracking &amp; Redirect</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="text-body-secondary small mb-2">
                    Tag <code>{email}</code> tidak perlu diubah karena akan otomatis diganti dengan email penerima saat proses pengiriman berlangsung.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Link</th>
                                <th>Template URL</th>
                                <th class="text-end">Salin</th>
                            </tr>
                        </thead>
                        <tbody id="redirectListBody"></tbody>
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
    let cmEditor = null;
    let previewTimer = null;

    document.addEventListener('DOMContentLoaded', () => {
        const textArea = document.getElementById('codeMirrorTextarea');
        cmEditor = CodeMirror.fromTextArea(textArea, {
            lineNumbers: true,
            mode: 'xml',
            htmlMode: true,
            matchBrackets: true,
            theme: 'dracula',
            lineWrapping: true
        });

        cmEditor.on('change', () => {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(refreshLivePreview, 350);
        });
    });

    function refreshLivePreview() {
        if (!cmEditor) return;
        const rawHtml = cmEditor.getValue();
        const frame = document.getElementById('livePreviewFrame');
        if (!frame) return;
        const doc = frame.contentDocument || frame.contentWindow.document;
        doc.open();
        doc.write(rawHtml || '<div style="font-family:sans-serif;padding:2rem;color:#94a3b8;text-align:center;">Ketik kode HTML di sebelah kiri untuk melihat pratinjau langsung.</div>');
        doc.close();
    }

    function showEditorAlert(msg, type = 'success') {
        document.getElementById('editorAlertBox').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show py-2 px-3 small mb-2" role="alert">
                ${msg}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

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

    async function openHtmlEditor(contentId) {
        document.getElementById('activeEditorContentId').value = contentId;
        document.getElementById('editorAlertBox').innerHTML = '';
        document.getElementById('assetListContainer').innerHTML = '<div class="text-body-secondary small py-3 text-center">Memuat data...</div>';
        showModal('modalHtmlEditor');

        try {
            const res = await csRequest(`{{ url('contents') }}/${contentId}/editor`, {
                method: 'GET'
            });
            const d = res.data;
            document.getElementById('editorMetaSubtitle').textContent = `${d.materi_name} | Subject: ${d.subject} | Jadwal: ${d.formatted_date} ${d.formatted_time}`;
            cmEditor.setValue(d.body_content || '');
            setTimeout(() => {
                cmEditor.refresh();
                refreshLivePreview();
            }, 200);

            renderAssets(d.assets || []);
            renderRedirects(d.redirects || []);
        } catch (err) {
            showEditorAlert(err.message, 'danger');
        }
    }

    function renderAssets(assets) {
        const container = document.getElementById('assetListContainer');
        if (!assets.length) {
            container.innerHTML = '<div class="text-body-secondary small py-3 text-center">Belum ada gambar diunggah pada materi ini.</div>';
            return;
        }

        let html = '<div class="d-flex flex-column gap-2">';
        assets.forEach((item) => {
            html += `
                <div class="border rounded p-2 bg-body-tertiary">
                    <img src="${item.local_url}" alt="${item.name}" class="w-100 rounded bg-secondary-subtle mb-2" style="height: 64px; object-fit: contain;">
                    <div class="small text-truncate fw-medium mb-1" title="${item.name}">${item.name}</div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-0" onclick="copyText('${item.local_url}')">
                            <i class="bi bi-clipboard"></i> URL Lokal/Aktif
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-0" onclick="copyText('${item.live_url}')">
                            <i class="bi bi-globe"></i> URL Live
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function renderRedirects(redirects) {
        let html = '';
        redirects.forEach((r) => {
            html += `
                <tr>
                    <td class="fw-medium">${r.name}</td>
                    <td><code class="small">${r.url}</code></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="copyText('${r.url}')">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </td>
                </tr>
            `;
        });
        document.getElementById('redirectListBody').innerHTML = html;
    }

    function copyText(text) {
        navigator.clipboard.writeText(text).then(() => {
            showEditorAlert('URL berhasil disalin ke clipboard: <code>' + text + '</code>', 'info');
        });
    }

    document.getElementById('formUploadAsset').addEventListener('submit', async (e) => {
        e.preventDefault();
        const contentId = document.getElementById('activeEditorContentId').value;
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
        const contentId = document.getElementById('activeEditorContentId').value;
        const bodyContent = cmEditor.getValue();
        const btn = e.currentTarget;
        btn.disabled = true;

        try {
            const res = await csRequest(`{{ url('contents') }}/${contentId}/body`, {
                method: 'POST',
                data: { body_content: bodyContent }
            });
            btn.disabled = false;
            showEditorAlert(res.message, 'success');
        } catch (err) {
            btn.disabled = false;
            showEditorAlert(err.message, 'danger');
        }
    });

    document.getElementById('btnPushLive').addEventListener('click', async (e) => {
        if (!confirm('Sinkronkan kode HTML dan seluruh gambar lokal materi ini ke Server Live (mbcontentservices) sekarang?')) {
            return;
        }
        const contentId = document.getElementById('activeEditorContentId').value;
        const btn = e.currentTarget;
        btn.disabled = true;

        try {
            await csRequest(`{{ url('contents') }}/${contentId}/body`, {
                method: 'POST',
                data: { body_content: cmEditor.getValue() }
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

    document.getElementById('btnCloseEditor').addEventListener('click', () => {
        hideModal('modalHtmlEditor');
    });
</script>
@endpush
