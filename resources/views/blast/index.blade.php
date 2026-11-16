@extends('layouts.app')

@section('title', 'Begin Blast — ' . $brand->name)
@section('page_title', 'Begin Blast: ' . $brand->name)

@section('content')
@php
    $isReady = $brand->isReadyToBlast();
    $waitMinutes = $brand->remainingWaitMinutes();
@endphp

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Workspace
        </a>
        <a href="{{ route('contents.index', $brand->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-code-square me-1"></i> Content Management
        </a>
        <a href="{{ route('settings.blastrules') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-sliders me-1"></i> Atur Blast Rules / Email Tester
        </a>
    </div>

    <div class="small text-body-secondary">
        Kuota per Batch: <strong class="text-body">{{ $brand->blast_limit ?: 'Tanpa Batas' }} email</strong>
        &middot; Interval Jeda: <strong class="text-body">{{ $brand->blast_hour_interval }} jam</strong>
        &middot; Pointer Terakhir: <code>#{{ $brand->last_email_id ?: 0 }}</code>
    </div>
</div>

@if (!$isReady)
    <div class="alert alert-warning d-flex align-items-center justify-content-between py-2 px-3 small mb-3" role="alert">
        <div>
            <i class="bi bi-hourglass-split me-1"></i>
            <strong>Jeda Waktu Tunggu Aktif:</strong> Pengiriman massal (Blast) berikutnya baru tersedia dalam <strong>{{ $waitMinutes }} menit</strong> (Blast terakhir: {{ $brand->last_blast_time }}). Anda tetap dapat melakukan <strong>Send Test</strong> atau <strong>Simulasi</strong> kapan saja.
        </div>
    </div>
@endif

<div class="row g-3">
    <!-- Kolom Konfigurasi Pengiriman -->
    <div class="col-12 col-lg-4">
        <section class="card p-3 h-100 d-flex flex-column justify-content-between shadow-sm" aria-label="Konfigurasi Pengiriman">
            <div>
                <h2 class="h6 fw-bold mb-3">1. Pilih Materi &amp; Aturan</h2>

                <div class="mb-3">
                    <label for="selectContent" class="form-label small fw-semibold">Materi Konten EDM</label>
                    <select id="selectContent" class="form-select">
                        <option value="">-- Pilih Materi Konten --</option>
                        @foreach ($contents as $c)
                            <option
                                value="{{ $c->id }}"
                                data-subject="{{ $c->subject }}"
                                data-date="{{ $c->formatted_date }}"
                                data-time="{{ $c->formatted_time }}"
                            >
                                [{{ $c->formatted_date }} {{ $c->formatted_time }}] {{ $c->materi_name }} — {{ $c->subject }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold d-block">Pilih Blast Rule</label>
                    <div class="d-flex flex-wrap gap-2" id="ruleButtonsGroup">
                        @forelse ($brand->activeBlastRules as $rule)
                            @php
                                $relayCount = $rule->activeDetails->filter(function($d) { return $d->relay && $d->relay->flag === 'Y'; })->count();
                            @endphp
                            <button
                                type="button"
                                class="btn btn-sm {{ $rule->type === 'blast' ? 'btn-outline-danger' : 'btn-outline-secondary' }} btn-select-rule"
                                data-rule-id="{{ $rule->id }}"
                                data-rule-name="{{ $rule->name }}"
                                data-rule-type="{{ $rule->type }}"
                                data-relay-count="{{ $relayCount }}"
                            >
                                <i class="bi {{ $rule->type === 'blast' ? 'bi-megaphone-fill' : 'bi-flask' }} me-1"></i>
                                {{ $rule->name }}
                                <span class="badge bg-body-tertiary text-body border ms-1">{{ $relayCount }} relay</span>
                            </button>
                        @empty
                            <div class="text-body-secondary small">Belum ada rule aktif pada brand ini.</div>
                        @endforelse
                    </div>
                </div>

                <div class="p-2 rounded border bg-body-tertiary mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="switchSimulate"
                            {{ !empty($isStagingMode) ? 'checked' : '' }}
                        >
                        <label class="form-check-label small fw-semibold" for="switchSimulate">
                            Mode Simulasi (Sandbox / Tanpa Kirim Keluar)
                        </label>
                    </div>
                    <div class="text-body-secondary mt-1" style="font-size: 0.75rem;" id="simulateHintText">
                        @if(!empty($isStagingMode))
                            Anda login dengan akun <code>dev</code>: Untuk <strong>Send Test</strong>, matikan saklar ini jika ingin mengirim email sungguhan ke inbox tester Anda. Untuk <strong>Blast Pelanggan</strong>, Mode Simulasi otomatis dikunci aktif demi keamanan.
                        @else
                            Jika diaktifkan, putaran relay dan progress bar tetap berjalan normal tanpa benar-benar mengirim email ke SMTP.
                        @endif
                    </div>
                </div>
            </div>

            <div class="border-top pt-3">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary flex-grow-1" id="btnStartBlast" disabled>
                        <i class="bi bi-send-fill me-1"></i> Mulai Pengiriman
                    </button>
                    <button type="button" class="btn btn-outline-danger d-none" id="btnStopBlast">
                        <i class="bi bi-stop-circle me-1"></i> Hentikan
                    </button>
                </div>
            </div>
        </section>
    </div>

    <!-- Kolom Progress & Daftar Penerima -->
    <div class="col-12 col-lg-8">
        <section class="card h-100 d-flex flex-column shadow-sm" aria-label="Daftar Penerima dan Status Pengiriman">
            <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h2 class="h6 fw-bold mb-0" id="recipientTableTitle">2. Daftar Penerima &amp; Status Eksekusi</h2>
                    <div class="text-body-secondary small" id="recipientSubtitle">Pilih Blast Rule di sebelah kiri untuk memuat daftar penerima.</div>
                </div>
                <div class="d-flex align-items-center gap-3 small">
                    <span>Total: <strong id="countTotal">0</strong></span>
                    <span class="text-success">Sukses: <strong id="countSuccess">0</strong></span>
                    <span class="text-danger">Gagal: <strong id="countFail">0</strong></span>
                </div>
            </div>

            <!-- Progress Bar Panel -->
            <div class="px-3 py-2 border-bottom bg-body-tertiary d-none" id="progressPanel">
                <div class="d-flex justify-content-between small mb-1">
                    <span id="progressStatusText" class="fw-medium">Menyiapkan pengiriman...</span>
                    <span id="progressPercentText" class="fw-bold">0%</span>
                </div>
                <div class="progress" style="height: 10px;" role="progressbar" aria-label="Progress Pengiriman Email" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="blastProgressBar" style="width: 0%"></div>
                </div>
            </div>

            <div class="table-responsive" style="max-height: 430px;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Penerima</th>
                            <th>SMTP Relay Pengirim (Round-Robin)</th>
                            <th style="width: 150px;">Waktu</th>
                            <th style="width: 160px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="recipientTableBody">
                        <tr>
                            <td colspan="5" class="text-center text-body-secondary py-4">
                                Belum ada daftar penerima yang dimuat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<!-- Riwayat Blast Log Terakhir -->
<section class="card mt-3 shadow-sm" aria-label="Riwayat Eksekusi Blast">
    <div class="card-header bg-transparent py-2">
        <h2 class="h6 fw-bold mb-0">Riwayat Eksekusi Terakhir ({{ $brand->name }})</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Waktu Eksekusi</th>
                    <th>Materi Konten</th>
                    <th>Pointer Email Terakhir</th>
                    <th>Status</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentLogs as $log)
                    <tr>
                        <td><code>{{ $log->created_at }}</code></td>
                        <td>{{ optional($log->content)->materi_name ?: '-' }} — <span class="text-body-secondary">{{ optional($log->content)->subject }}</span></td>
                        <td><code>#{{ $log->last_email_id }}</code></td>
                        <td>
                            @if ($log->status === 'completed')
                                <span class="badge text-bg-success">Completed</span>
                            @elseif ($log->status === 'simulated')
                                <span class="badge text-bg-warning">Simulated (Sandbox)</span>
                            @elseif ($log->status === 'test_sent')
                                <span class="badge text-bg-info">Send Test</span>
                            @else
                                <span class="badge text-bg-secondary">{{ $log->status }}</span>
                            @endif
                        </td>
                        <td>{{ $log->created_who ?: 'Admin' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-3">Belum ada riwayat log pengiriman pada brand ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script>
    const isStagingAccount = @json(!empty($isStagingMode));
    let selectedRule = null;
    let recipientsList = [];
    let isRunningBlast = false;
    let stopRequested = false;

    const selectContentEl = document.getElementById('selectContent');
    const switchSimulateEl = document.getElementById('switchSimulate');
    const btnStartBlastEl = document.getElementById('btnStartBlast');
    const btnStopBlastEl = document.getElementById('btnStopBlast');
    const progressPanelEl = document.getElementById('progressPanel');
    const blastProgressBarEl = document.getElementById('blastProgressBar');
    const progressStatusTextEl = document.getElementById('progressStatusText');
    const progressPercentTextEl = document.getElementById('progressPercentText');
    const recipientTableBodyEl = document.getElementById('recipientTableBody');
    const recipientSubtitleEl = document.getElementById('recipientSubtitle');
    const countTotalEl = document.getElementById('countTotal');
    const countSuccessEl = document.getElementById('countSuccess');
    const countFailEl = document.getElementById('countFail');

    function checkCanStart() {
        const hasContent = selectContentEl.value !== '';
        const hasRule = selectedRule !== null;
        const hasRecipients = recipientsList.length > 0;
        btnStartBlastEl.disabled = !(hasContent && hasRule && hasRecipients && !isRunningBlast);
    }

    selectContentEl.addEventListener('change', checkCanStart);

    switchSimulateEl.addEventListener('change', () => {
        if (selectedRule) {
            loadRecipientsForRule(selectedRule.id);
        }
    });

    document.querySelectorAll('.btn-select-rule').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (isRunningBlast) return;

            document.querySelectorAll('.btn-select-rule').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');

            const ruleId = btn.getAttribute('data-rule-id');
            const ruleName = btn.getAttribute('data-rule-name');
            const ruleType = btn.getAttribute('data-rule-type');

            selectedRule = { id: ruleId, name: ruleName, type: ruleType };

            if (isStagingAccount && ruleType === 'blast') {
                switchSimulateEl.checked = true;
                switchSimulateEl.disabled = true;
            } else {
                switchSimulateEl.disabled = false;
            }

            loadRecipientsForRule(ruleId);
        });
    });

    async function loadRecipientsForRule(ruleId) {
        const simulate = switchSimulateEl.checked ? 1 : 0;
        recipientTableBodyEl.innerHTML = '<tr><td colspan="5" class="text-center text-body-secondary py-4">Memuat daftar penerima...</td></tr>';
        recipientsList = [];
        checkCanStart();

        try {
            const res = await csRequest(@json(route('blast.recipients', $brand->id)), {
                method: 'POST',
                data: { rule_id: ruleId, simulate: simulate }
            });

            recipientsList = res.data || [];
            countTotalEl.textContent = recipientsList.length;
            countSuccessEl.textContent = '0';
            countFailEl.textContent = '0';
            recipientSubtitleEl.innerHTML = `Rule: <strong>${res.rule_name}</strong> (${res.rule_type.toUpperCase()}) &middot; ${res.relay_count} SMTP Relay aktif siap berotasi.`;

            if (!recipientsList.length) {
                recipientTableBodyEl.innerHTML = '<tr><td colspan="5" class="text-center text-body-secondary py-4">Tidak ada penerima aktif ditemukan untuk rule ini.</td></tr>';
                checkCanStart();
                return;
            }

            let rows = '';
            recipientsList.forEach((r, idx) => {
                rows += `
                    <tr id="recip-row-${r.id}">
                        <td class="text-body-secondary">${idx + 1}</td>
                        <td>
                            <div class="fw-medium">${r.email}</div>
                            <div class="text-body-secondary" style="font-size: 0.75rem;">${r.name}</div>
                        </td>
                        <td class="small text-body-secondary" id="recip-relay-${r.id}">Menunggu giliran...</td>
                        <td class="small text-body-secondary" id="recip-time-${r.id}">-</td>
                        <td id="recip-status-${r.id}">
                            <span class="badge bg-body-tertiary text-body border">Siap</span>
                        </td>
                    </tr>
                `;
            });
            recipientTableBodyEl.innerHTML = rows;
            checkCanStart();
        } catch (err) {
            recipientTableBodyEl.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${err.message}</td></tr>`;
            checkCanStart();
        }
    }

    btnStartBlastEl.addEventListener('click', () => {
        if (!selectedRule || !recipientsList.length) return;
        const contentId = selectContentEl.value;
        if (!contentId) return;

        const isSim = switchSimulateEl.checked;
        const modeLabel = isSim ? 'MODE SIMULASI (SANDBOX)' : (selectedRule.type === 'blast' ? 'MODE BLAST PELANGGAN ASLI' : 'MODE SEND TEST');

        if (!confirm(`Mulai pengiriman ${recipientsList.length} email menggunakan rule "${selectedRule.name}" dalam ${modeLabel}?`)) {
            return;
        }

        isRunningBlast = true;
        stopRequested = false;
        btnStartBlastEl.disabled = true;
        btnStopBlastEl.classList.remove('d-none');
        progressPanelEl.classList.remove('d-none');
        countSuccessEl.textContent = '0';
        countFailEl.textContent = '0';

        processNextRecipient(0, contentId, selectedRule.id, isSim ? 1 : 0, 0, 0);
    });

    btnStopBlastEl.addEventListener('click', () => {
        if (confirm('Hentikan proses pengiriman setelah email saat ini selesai?')) {
            stopRequested = true;
            progressStatusTextEl.textContent = 'Menghentikan proses pengiriman...';
        }
    });

    async function processNextRecipient(index, contentId, ruleId, simulate, okCount, failCount) {
        if (stopRequested || index >= recipientsList.length) {
            isRunningBlast = false;
            btnStopBlastEl.classList.add('d-none');
            blastProgressBarEl.classList.remove('progress-bar-animated');
            progressStatusTextEl.textContent = stopRequested ? 'Pengiriman dihentikan oleh pengguna.' : 'Seluruh antrean pengiriman telah selesai.';
            checkCanStart();
            return;
        }

        const item = recipientsList[index];
        const isLast = (index === recipientsList.length - 1) ? 1 : 0;

        progressStatusTextEl.textContent = `Mengirim ke ${item.email} (${index + 1} dari ${recipientsList.length})...`;
        const statusCell = document.getElementById(`recip-status-${item.id}`);
        if (statusCell) {
            statusCell.innerHTML = '<span class="badge text-bg-primary">Mengirim...</span>';
        }

        try {
            const res = await csRequest(@json(route('blast.sendItem', $brand->id)), {
                method: 'POST',
                data: {
                    content_id: contentId,
                    rule_id: ruleId,
                    recipient_id: item.id,
                    recipient_email: item.email,
                    relay_index: index,
                    is_last: isLast,
                    simulate: simulate
                }
            });

            const r = res.result;
            if (r.success) {
                okCount++;
                countSuccessEl.textContent = okCount;
                const badgeClass = r.simulated ? 'text-bg-warning' : 'text-bg-success';
                if (statusCell) {
                    statusCell.innerHTML = `<span class="badge ${badgeClass}">${r.message}</span>`;
                }
            } else {
                failCount++;
                countFailEl.textContent = failCount;
                if (statusCell) {
                    statusCell.innerHTML = `<span class="badge text-bg-danger" title="${r.message}">Gagal</span>`;
                }
            }

            const relayCell = document.getElementById(`recip-relay-${item.id}`);
            const timeCell = document.getElementById(`recip-time-${item.id}`);
            if (relayCell) {
                relayCell.innerHTML = `<div>${r.relay_used}</div><div style="font-size:0.7rem;">${r.from_used}</div>`;
            }
            if (timeCell) {
                timeCell.textContent = r.sent_at;
            }

            const pct = Math.round(((index + 1) / recipientsList.length) * 100);
            blastProgressBarEl.style.width = pct + '%';
            progressPercentTextEl.textContent = pct + '%';

            const delayMs = (res.delay_seconds || 0) * 1000;
            if (delayMs > 0 && index < recipientsList.length - 1 && !stopRequested) {
                progressStatusTextEl.textContent = `Rotasi 1 putaran relay selesai. Jeda anti-spam ${res.delay_seconds} detik...`;
                setTimeout(() => {
                    processNextRecipient(index + 1, contentId, ruleId, simulate, okCount, failCount);
                }, delayMs);
            } else {
                processNextRecipient(index + 1, contentId, ruleId, simulate, okCount, failCount);
            }
        } catch (err) {
            failCount++;
            countFailEl.textContent = failCount;
            if (statusCell) {
                statusCell.innerHTML = `<span class="badge text-bg-danger">${err.message}</span>`;
            }
            processNextRecipient(index + 1, contentId, ruleId, simulate, okCount, failCount);
        }
    }
</script>
@endpush
