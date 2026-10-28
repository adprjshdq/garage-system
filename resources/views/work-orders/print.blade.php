<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Kerja (SPK) — {{ $workOrder->wo_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 13px; color: #000; }
        .spk-box { max-width: 800px; margin: auto; padding: 25px; border: 1px solid #ddd; }
        @media print {
            .no-print { display: none !important; }
            .spk-box { border: none; padding: 0; }
        }
    </style>
</head>
<body class="bg-light py-4">
    <div class="spk-box bg-white">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <div>
                <h4 class="fw-bold mb-0 text-primary">MotoCare Workshop</h4>
                <div class="small text-muted">{{ \App\Models\WorkshopSetting::get('workshop_address') }}</div>
                <div class="small text-muted">Telp: {{ \App\Models\WorkshopSetting::get('workshop_phone') }}</div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-0">SURAT PERINTAH KERJA (SPK)</h5>
                <div class="font-monospace fw-bold fs-6">{{ $workOrder->wo_number }}</div>
                <div class="small text-muted">Tanggal: {{ $workOrder->created_at->format('d/m/Y H:i') }} WIB</div>
            </div>
        </div>

        <!-- Info Unit & Pelanggan -->
        <div class="row g-3 mb-3 pb-3 border-bottom">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width: 110px;">Pelanggan</td><td class="fw-bold">: {{ $workOrder->customer->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">No. Kontak</td><td>: {{ $workOrder->customer->phone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Alamat</td><td>: {{ $workOrder->customer->address ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width: 110px;">No. Polisi</td><td class="fw-bold fs-6">: {{ $workOrder->vehicle->license_plate ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Kendaraan</td><td>: {{ $workOrder->vehicle->brand ?? '' }} {{ $workOrder->vehicle->model ?? '' }} ({{ $workOrder->vehicle->color ?? '' }})</td></tr>
                    <tr><td class="text-muted">Kilometer</td><td>: {{ number_format($workOrder->mileage, 0, ',', '.') }} km</td></tr>
                </table>
            </div>
        </div>

        <div class="p-2 bg-light rounded mb-3">
            <div class="fw-bold small">Keluhan Utama Pelanggan:</div>
            <div>{{ $workOrder->customer_complaint }}</div>
            @if($workOrder->inspection_result)
                <div class="fw-bold small mt-2">Diagnosa Awal / Rekomendasi Teknisi:</div>
                <div>{{ $workOrder->inspection_result }}</div>
            @endif
        </div>

        <!-- Tabel Pekerjaan -->
        <h6 class="fw-bold mb-2">Daftar Pekerjaan & Kebutuhan Spare Part:</h6>
        <table class="table table-bordered table-sm mb-3">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;" class="text-center">No</th>
                    <th>Deskripsi Jasa Servis / Suku Cadang</th>
                    <th style="width: 100px;" class="text-center">Tipe</th>
                    <th style="width: 60px;" class="text-center">Qty</th>
                    <th style="width: 130px;" class="text-end">Harga Satuan</th>
                    <th style="width: 130px;" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workOrder->items as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td class="text-center">{{ $item->item_type === 'service' ? 'Jasa' : 'Part' }}</td>
                        <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                        <td class="text-end">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="5" class="text-end">Total Estimasi:</td>
                    <td class="text-end">Rp{{ number_format($workOrder->actual_total_cost > 0 ? $workOrder->actual_total_cost : $workOrder->estimated_cost, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Mekanik -->
        <div class="mb-4">
            <span class="small fw-bold">Mekanik yang Ditugaskan:</span>
            @foreach($workOrder->mechanics as $mech)
                <span class="badge bg-light text-dark border me-1">{{ $mech->name }}</span>
            @endforeach
        </div>

        <!-- Tanda Tangan -->
        <div class="row text-center mt-5 pt-3">
            <div class="col-4">
                <div>Service Advisor,</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold text-decoration-underline">{{ $workOrder->creator->name ?? 'Service Advisor' }}</div>
            </div>
            <div class="col-4">
                <div>Mekanik Bertugas,</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold text-decoration-underline">{{ $workOrder->mechanics->first()->name ?? 'Teknisi' }}</div>
            </div>
            <div class="col-4">
                <div>Persetujuan Pelanggan,</div>
                <div style="height: 60px;">
                    @if($workOrder->latestApproval && $workOrder->latestApproval->signature_data)
                        <img src="{{ $workOrder->latestApproval->signature_data }}" style="max-height: 55px;" alt="Signature">
                    @endif
                </div>
                <div class="fw-bold text-decoration-underline">{{ $workOrder->customer->name ?? 'Pelanggan' }}</div>
            </div>
        </div>

        <div class="no-print mt-4 pt-3 border-top text-center">
            <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer me-1"></i> Cetak SPK</button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm ms-2">Tutup</button>
        </div>
    </div>
</body>
</html>

