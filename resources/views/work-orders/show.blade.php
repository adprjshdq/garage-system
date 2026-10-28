@extends('layouts.app')

@section('title', 'Detail Work Order — ' . $workOrder->wo_number)

@section('content')
<!-- Header & Action Buttons -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold text-dark mb-0">{{ $workOrder->wo_number }}</h4>
            {!! $workOrder->status_badge !!}
        </div>
        <p class="text-muted small mb-0 mt-1">Dibuat oleh: <strong>{{ $workOrder->creator->name ?? '-' }}</strong> pada {{ $workOrder->created_at->format('d/m/Y H:i') }} WIB</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('work-orders.print', $workOrder->id) }}" target="_blank" class="btn btn-sm btn-light border">
            <i class="bi bi-printer me-1"></i> Cetak SPK
        </a>

        <!-- Status Transitions -->
        @if(in_array($workOrder->status, ['draft', 'waiting_approval']))
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approvalModal">
                <i class="bi bi-pen me-1"></i> Persetujuan Pelanggan
            </button>
        @endif

        @if(in_array($workOrder->status, ['approved', 'waiting_parts']))
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#startWorkModal">
                <i class="bi bi-play-circle me-1"></i> Mulai Pengerjaan
            </button>
        @endif

        @if($workOrder->status === 'in_progress')
            <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#inspectionModal">
                <i class="bi bi-clipboard-check me-1"></i> Pemeriksaan Akhir
            </button>
        @endif

        @if(in_array($workOrder->status, ['completed', 'final_inspection']) && !$workOrder->invoice)
            <a href="{{ route('invoices.create', ['work_order_id' => $workOrder->id]) }}" class="btn btn-sm btn-mc-primary">
                <i class="bi bi-receipt me-1"></i> Terbitkan Invoice
            </a>
        @endif

        @if(!in_array($workOrder->status, ['closed', 'cancelled', 'invoiced']))
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="bi bi-x-circle me-1"></i> Batalkan WO
            </button>
        @endif

        <a href="{{ route('work-orders.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Progress Bar Alur Status Operasional -->
<div class="card card-custom p-3 mb-4">
    <div class="d-flex justify-content-between text-center small fw-semibold">
        <span class="{{ in_array($workOrder->status, ['draft', 'waiting_approval', 'approved', 'in_progress', 'final_inspection', 'completed', 'invoiced', 'closed']) ? 'text-primary' : 'text-muted' }}">
            <i class="bi bi-pencil-square d-block fs-5"></i> 1. Estimasi
        </span>
        <span class="{{ in_array($workOrder->status, ['approved', 'in_progress', 'final_inspection', 'completed', 'invoiced', 'closed']) ? 'text-primary' : 'text-muted' }}">
            <i class="bi bi-check2-circle d-block fs-5"></i> 2. Disetujui
        </span>
        <span class="{{ in_array($workOrder->status, ['in_progress', 'final_inspection', 'completed', 'invoiced', 'closed']) ? 'text-primary' : 'text-muted' }}">
            <i class="bi bi-gear-wide-connected d-block fs-5"></i> 3. Pengerjaan
        </span>
        <span class="{{ in_array($workOrder->status, ['final_inspection', 'completed', 'invoiced', 'closed']) ? 'text-primary' : 'text-muted' }}">
            <i class="bi bi-clipboard-check d-block fs-5"></i> 4. Uji Akhir
        </span>
        <span class="{{ in_array($workOrder->status, ['completed', 'invoiced', 'closed']) ? 'text-success' : 'text-muted' }}">
            <i class="bi bi-flag-fill d-block fs-5"></i> 5. Selesai
        </span>
        <span class="{{ in_array($workOrder->status, ['invoiced', 'closed']) ? 'text-success' : 'text-muted' }}">
            <i class="bi bi-receipt-cutoff d-block fs-5"></i> 6. Kasir
        </span>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Info Motor & Pemilik -->
    <div class="col-md-6">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-scooter text-primary me-1"></i> Data Motor & Pelanggan</h6>
            <div class="row g-2">
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Nomor Polisi</small>
                    <span class="badge bg-dark font-monospace fs-6">{{ $workOrder->vehicle->license_plate ?? '-' }}</span>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Kilometer Servis</small>
                    <div class="fw-bold text-primary">{{ number_format($workOrder->mileage, 0, ',', '.') }} km</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Merek & Model</small>
                    <div class="fw-semibold">{{ $workOrder->vehicle->brand ?? '-' }} {{ $workOrder->vehicle->model ?? '' }}</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Warna & Tahun</small>
                    <div>{{ $workOrder->vehicle->color ?: '-' }} / {{ $workOrder->vehicle->year ?: '-' }}</div>
                </div>
                <div class="col-12 pt-2 border-top">
                    <small class="text-muted d-block">Pemilik Kendaraan</small>
                    <div class="fw-bold">{{ $workOrder->customer->name ?? '-' }} (<i class="bi bi-telephone"></i> {{ $workOrder->customer->phone ?? '-' }})</div>
                    <small class="text-muted">{{ $workOrder->customer->address ?? '' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Keluhan & Diagnosa Teknis -->
    <div class="col-md-6">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clipboard-pulse text-info me-1"></i> Keluhan & Analisa Teknis</h6>
            <div class="mb-3">
                <small class="text-muted d-block">Keluhan Pelanggan</small>
                <div class="p-2 bg-light rounded text-dark">{{ $workOrder->customer_complaint }}</div>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Hasil Pemeriksaan / Diagnosa</small>
                <div class="small text-secondary">{{ $workOrder->inspection_result ?: 'Tidak ada catatan diagnosa awal.' }}</div>
            </div>
            @if($workOrder->notes)
                <div>
                    <small class="text-muted d-block">Catatan Khusus</small>
                    <div class="small fst-italic text-secondary">{{ $workOrder->notes }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Rincian Pekerjaan (Jasa & Spare Part) -->
<div class="card card-custom p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-check text-primary me-1"></i> Rincian Jasa & Spare Part</h5>
        <span class="badge bg-light text-dark border">{{ $workOrder->items->count() }} Item Pekerjaan</span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 100px;">Tipe</th>
                    <th>Nama Pekerjaan / Komponen</th>
                    <th style="width: 90px;">Qty</th>
                    <th style="width: 140px;">Harga Satuan</th>
                    <th style="width: 120px;">Diskon</th>
                    <th style="width: 140px;">Subtotal</th>
                    <th style="width: 150px;">Status Part</th>
                    <th style="width: 120px;" class="text-end">Aksi Fisik</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workOrder->items as $item)
                    <tr>
                        <td>
                            @if($item->item_type === 'service')
                                <span class="badge bg-primary-subtle text-primary border">Jasa Servis</span>
                            @else
                                <span class="badge bg-success-subtle text-success border">Spare Part</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $item->item_name }}</div>
                            @if($item->item_type === 'part' && $item->sparePart)
                                <small class="text-muted">SKU: {{ $item->sparePart->sku }} &bull; Lokasi: {{ $item->sparePart->rack_location ?: '-' }}</small>
                            @endif
                        </td>
                        <td>{{ number_format($item->quantity, 0) }}</td>
                        <td>Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td>{{ $item->discount > 0 ? 'Rp' . number_format($item->discount, 0, ',', '.') : '-' }}</td>
                        <td class="fw-bold text-dark">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        <td>
                            @if($item->item_type === 'part')
                                @switch($item->fulfillment_status)
                                    @case('pending')
                                        <span class="badge bg-secondary">Pending</span>
                                        @break
                                    @case('reserved')
                                        <span class="badge bg-warning text-dark">Dialokasikan (Reserved)</span>
                                        @break
                                    @case('issued')
                                        <span class="badge bg-success">Keluar Fisik (Dipakai)</span>
                                        @break
                                    @case('returned')
                                        <span class="badge bg-info text-dark">Dikembalikan</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark border">{{ $item->fulfillment_status }}</span>
                                @endswitch
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($item->item_type === 'part' && in_array($item->fulfillment_status, ['pending', 'reserved']) && in_array($workOrder->status, ['approved', 'in_progress']))
                                <form action="{{ route('work-orders.issue-part', ['work_order' => $workOrder->id, 'item' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Keluarkan barang fisik dari gudang">
                                        <i class="bi bi-box-arrow-up me-1"></i> Keluar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="5" class="text-end">Total Biaya Aktual:</td>
                    <td colspan="3" class="text-primary fs-5">Rp{{ number_format($workOrder->actual_total_cost > 0 ? $workOrder->actual_total_cost : $workOrder->estimated_cost, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row g-3">
    <!-- Mekanik Bertugas -->
    <div class="col-md-6">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-wrench text-warning me-1"></i> Tim Mekanik Bertugas</h6>
            <div class="list-group list-group-flush small">
                @forelse($workOrder->mechanics as $mech)
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">{{ $mech->name }}</div>
                            <small class="text-muted">Keahlian: {{ $mech->specialization }} &bull; {{ $mech->pivot->role_description }}</small>
                        </div>
                        <div class="text-end">
                            @if($mech->pivot->started_at)
                                <span class="badge bg-light text-dark border">Mulai: {{ \Carbon\Carbon::parse($mech->pivot->started_at)->format('H:i') }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center py-3">Belum ada mekanik yang ditugaskan.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Riwayat Persetujuan Pelanggan -->
    <div class="col-md-6">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-pen text-success me-1"></i> Persetujuan Pelanggan (Estimasi)</h6>
            @if($workOrder->latestApproval)
                <div class="alert alert-light border p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-success">Versi {{ $workOrder->latestApproval->version }} &bull; Disetujui</span>
                        <small class="text-muted">{{ $workOrder->latestApproval->approved_at->format('d/m/Y H:i') }} WIB</small>
                    </div>
                    <div>Metode: <strong>{{ $workOrder->latestApproval->method_label }}</strong></div>
                    <div>Estimasi Disetujui: <strong>Rp{{ number_format($workOrder->latestApproval->estimated_amount, 0, ',', '.') }}</strong></div>
                    <div>Dicatat oleh: {{ $workOrder->latestApproval->recorder->name ?? '-' }}</div>
                    @if($workOrder->latestApproval->notes)
                        <div class="small text-muted mt-2">Catatan: {{ $workOrder->latestApproval->notes }}</div>
                    @endif
                    @if($workOrder->latestApproval->signature_data)
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted d-block">Tanda Tangan Digital Pelanggan:</small>
                            <img src="{{ $workOrder->latestApproval->signature_data }}" alt="Tanda Tangan Pelanggan" style="max-height: 80px; border: 1px dashed #ccc;" class="rounded p-1 bg-white">
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-exclamation-circle fs-3 text-warning d-block mb-1"></i>
                    Belum ada persetujuan estimasi yang dicatat.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODALS -->
<!-- ============================================== -->

<!-- 1. Modal Persetujuan Pelanggan (Digital Signature Pad) -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('work-orders.approve', $workOrder->id) }}" method="POST" id="approvalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pen me-1 text-primary"></i> Persetujuan Estimasi Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border p-2 mb-3">
                        <div class="small text-muted">Total Nilai Estimasi:</div>
                        <div class="fs-4 fw-bold text-primary">Rp{{ number_format($workOrder->estimated_cost, 0, ',', '.') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Metode Persetujuan <span class="text-danger">*</span></label>
                        <select name="approval_method" id="approvalMethodSelect" class="form-select" required>
                            <option value="in_person">Tatap Muka Langsung (Lisan)</option>
                            <option value="phone_wa">Konfirmasi Telepon / WhatsApp</option>
                            <option value="digital_signature">Tanda Tangan Digital di Layar</option>
                        </select>
                    </div>

                    <!-- Digital Signature Pad Canvas -->
                    <div class="mb-3 d-none" id="signatureBox">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Tanda Tangan Pelanggan (Goreskan di kotak):</label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearSigBtn">Bersihkan Pad</button>
                        </div>
                        <canvas id="signatureCanvas" width="450" height="150" style="border: 2px dashed #94a3b8; border-radius: 8px; width: 100%; touch-action: none; background: #ffffff; cursor: crosshair;"></canvas>
                        <input type="hidden" name="signature_data" id="signatureDataInput">
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Catatan / Bukti Persetujuan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Disetujui via telepon pukul 10:15 WIB"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-semibold"><i class="bi bi-check-lg me-1"></i> Simpan Persetujuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Modal Mulai Pengerjaan (Pilih Mekanik) -->
<div class="modal fade" id="startWorkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('work-orders.start', $workOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-play-circle me-1 text-primary"></i> Mulai Pengerjaan & Tugaskan Mekanik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Pilih mekanik yang akan menangani pengerjaan servis untuk motor <strong>{{ $workOrder->vehicle->license_plate ?? '' }}</strong>:</p>
                    <label class="form-label small fw-semibold">Pilih Mekanik Bertugas <span class="text-danger">*</span></label>
                    <div class="border rounded p-3 bg-light">
                        @foreach($availableMechanics as $mech)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="mechanic_ids[]" value="{{ $mech->id }}" id="mech_{{ $mech->id }}"
                                    {{ $workOrder->mechanics->contains($mech->id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="mech_{{ $mech->id }}">
                                    <strong>{{ $mech->name }}</strong> <small class="text-muted">({{ $mech->specialization }})</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="bi bi-wrench me-1"></i> Mulai Pengerjaan Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Modal Pemeriksaan Akhir (Inspection Checklist) -->
<div class="modal fade" id="inspectionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('work-orders.inspect', $workOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-1 text-info"></i> Formulir Pemeriksaan Akhir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pemeriksaan Rem Depan & Belakang</label>
                        <select name="brake_check" class="form-select">
                            <option value="ok">Normal / Pakem (OK)</option>
                            <option value="need_attention">Perlu Perhatian Khusus</option>
                            <option value="bad">Bermasalah / Tidak Pakem</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pemeriksaan Mesin & CVT</label>
                        <select name="engine_check" class="form-select">
                            <option value="ok">Halus & Responsif (OK)</option>
                            <option value="need_attention">Masih Terdengar Suara Kasar</option>
                            <option value="bad">Bermasalah</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pemeriksaan Kelistrikan & Lampu</label>
                        <select name="electric_check" class="form-select">
                            <option value="ok">Semua Menyala Normal (OK)</option>
                            <option value="need_attention">Ada Indikator/Bohlam Redup</option>
                            <option value="bad">Ada Kelistrikan Mati</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pemeriksaan Tekanan Ban & Roda</label>
                        <select name="tires_check" class="form-select">
                            <option value="ok">Tekanan Sesuai & Putaran Lurus (OK)</option>
                            <option value="need_attention">Ban Mulai Botak</option>
                            <option value="bad">Velg Oleng / Ban Gundul</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan Hasil Uji Jalan (Test Drive)</label>
                        <textarea name="test_drive_notes" class="form-control" rows="2" placeholder="Contoh: Tarikan enteng, keluhan gredeg hilang total..."></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Status Kelulusan Servis <span class="text-danger">*</span></label>
                        <select name="is_passed" class="form-select fw-bold">
                            <option value="1" class="text-success">LULUS (Motor Siap Diambil / Ditagihkan)</option>
                            <option value="0" class="text-danger">BELUM LULUS (Perlu Pengerjaan Tambahan)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white fw-semibold">Simpan Hasil Pemeriksaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. Modal Pembatalan Work Order -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('work-orders.cancel', $workOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle me-1"></i> Batalkan Work Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Membatalkan Work Order akan melepaskan seluruh alokasi reservasi stok spare part kembali ke gudang.</p>
                    <label class="form-label small fw-semibold">Alasan Pembatalan <span class="text-danger">*</span></label>
                    <textarea name="cancellation_reason" class="form-control" rows="3" placeholder="Contoh: Pelanggan membatalkan servis karena urusan mendadak..." required></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Konfirmasi Pembatalan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Digital Signature Pad Canvas implementation
    const methodSelect = document.getElementById('approvalMethodSelect');
    const signatureBox = document.getElementById('signatureBox');
    const canvas = document.getElementById('signatureCanvas');
    const clearBtn = document.getElementById('clearSigBtn');
    const sigDataInput = document.getElementById('signatureDataInput');
    const approvalForm = document.getElementById('approvalForm');

    if (methodSelect && canvas) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        methodSelect.addEventListener('change', function() {
            if (this.value === 'digital_signature') {
                signatureBox.classList.remove('d-none');
            } else {
                signatureBox.classList.add('d-none');
            }
        });

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDraw(e) {
            isDrawing = true;
            const pos = getPos(e);
            lastX = pos.x;
            lastY = pos.y;
            e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
            e.preventDefault();
        }

        function stopDraw() {
            isDrawing = false;
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);

        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw);

        clearBtn?.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            sigDataInput.value = '';
        });

        approvalForm?.addEventListener('submit', function() {
            if (methodSelect.value === 'digital_signature') {
                sigDataInput.value = canvas.toDataURL('image/png');
            }
        });
    }
</script>
@endpush

