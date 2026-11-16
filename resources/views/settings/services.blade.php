@extends('layouts.app')

@section('title', 'Content Services')
@section('page_title', 'Content Services Configuration')

@section('content')
<section class="card shadow-sm" aria-label="Daftar Content Services">
    <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="h6 fw-bold mb-0">Daftar Endpoint Content Service</h2>
            <p class="text-body-secondary small mb-0">Direktori layanan API penampung file HTML dan asset gambar newsletter pada setiap server.</p>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddService">
            <i class="bi bi-plus-lg me-1"></i> Tambah Service
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Path Service</th>
                    <th>Server Terkait</th>
                    <th>Warna Indikator</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($services as $index => $s)
                    <tr>
                        <td class="text-body-secondary">{{ $index + 1 }}</td>
                        <td><code>{{ $s->service }}</code></td>
                        <td>{{ optional($s->server)->name ?: '-' }} (<code>{{ optional($s->server)->domain }}</code>)</td>
                        <td>
                            <span class="badge text-dark border" style="background-color: {{ $s->color ?: '#f1f5f9' }};">
                                {{ $s->color ?: '-' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary"
                                onclick="openEditService({{ $s->id }}, @json($s->service), {{ (int) $s->server_id }}, @json($s->color))"
                            >
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- Modal Tambah Service -->
<div class="modal fade" id="modalAddService" tabindex="-1" aria-labelledby="modalAddServiceLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="formAddService">
            <div class="modal-header">
                <h2 class="modal-title h6 fw-bold" id="modalAddServiceLabel">Tambah Content Service</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="addServiceName" class="form-label small fw-semibold">Path Service</label>
                    <input type="text" id="addServiceName" name="service" class="form-control" placeholder="mbcontentservices/" required>
                </div>
                <div class="mb-3">
                    <label for="addServiceServer" class="form-label small fw-semibold">Pilih Server</label>
                    <select id="addServiceServer" name="server_id" class="form-select" required>
                        @foreach ($servers as $srv)
                            <option value="{{ $srv->id }}">{{ $srv->name }} ({{ $srv->domain }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="addServiceColor" class="form-label small fw-semibold">Warna Badge</label>
                    <input type="color" id="addServiceColor" name="color" class="form-control form-control-color w-100" value="#7affe9">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-primary">Simpan Service</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Service -->
<div class="modal fade" id="modalEditService" tabindex="-1" aria-labelledby="modalEditServiceLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="formEditService">
            <input type="hidden" id="editServiceId">
            <div class="modal-header">
                <h2 class="modal-title h6 fw-bold" id="modalEditServiceLabel">Edit Content Service</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="editServiceName" class="form-label small fw-semibold">Path Service</label>
                    <input type="text" id="editServiceName" name="service" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="editServiceServer" class="form-label small fw-semibold">Pilih Server</label>
                    <select id="editServiceServer" name="server_id" class="form-select" required>
                        @foreach ($servers as $srv)
                            <option value="{{ $srv->id }}">{{ $srv->name }} ({{ $srv->domain }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="editServiceColor" class="form-label small fw-semibold">Warna Badge</label>
                    <input type="color" id="editServiceColor" name="color" class="form-control form-control-color w-100">
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
    document.getElementById('formAddService').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await csRequest(@json(route('settings.services.store')), {
                method: 'POST',
                form: e.currentTarget
            });
            window.location.reload();
        } catch (err) {
            alert(err.message);
        }
    });

    function openEditService(id, service, serverId, color) {
        document.getElementById('editServiceId').value = id;
        document.getElementById('editServiceName').value = service;
        document.getElementById('editServiceServer').value = serverId;
        document.getElementById('editServiceColor').value = color || '#7affe9';
        showModal('modalEditService');
    }

    document.getElementById('formEditService').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editServiceId').value;
        try {
            await csRequest(`{{ url('settings/services') }}/${id}`, {
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
