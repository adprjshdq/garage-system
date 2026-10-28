<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima Kendaraan — {{ $reception->reception_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 13px; color: #111; }
        .receipt-box { max-width: 780px; margin: auto; padding: 20px; border: 1px solid #ccc; }
        @media print {
            .no-print { display: none !important; }
            .receipt-box { border: none; padding: 0; }
        }
    </style>
</head>
<body class="bg-light py-4">
    <div class="receipt-box bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <div>
                <h4 class="fw-bold mb-0 text-primary">MotoCare Workshop</h4>
                <div class="small text-muted">{{ \App\Models\WorkshopSetting::get('workshop_address') }}</div>
                <div class="small text-muted">Telp: {{ \App\Models\WorkshopSetting::get('workshop_phone') }}</div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-0">TANDA TERIMA KENDARAAN</h5>
                <div class="font-monospace fw-bold">{{ $reception->reception_number }}</div>
                <div class="small text-muted">{{ $reception->reception_time->format('d/m/Y H:i') }} WIB</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width: 120px;">Pemilik</td><td class="fw-bold">: {{ $reception->customer->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">No. Telepon</td><td>: {{ $reception->customer->phone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Alamat</td><td>: {{ $reception->customer->address ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width: 120px;">No. Polisi</td><td class="fw-bold fs-6">: {{ $reception->vehicle->license_plate ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Motor</td><td>: {{ $reception->vehicle->brand ?? '' }} {{ $reception->vehicle->model ?? '' }}</td></tr>
                    <tr><td class="text-muted">Kilometer (ODO)</td><td>: {{ number_format($reception->mileage_in, 0, ',', '.') }} km</td></tr>
                </table>
            </div>
        </div>

        <div class="p-3 bg-light rounded mb-3">
            <div class="fw-bold mb-1">Keluhan Pelanggan:</div>
            <div>{{ $reception->customer_complaint }}</div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="small fw-bold">Kondisi Awal Fisik:</div>
                <div class="small text-muted">{{ $reception->initial_condition ?: 'Standar / Tidak ada cacat khusus' }}</div>
            </div>
            <div class="col-6">
                <div class="small fw-bold">Barang Bawaan Dititip:</div>
                <div class="small text-muted">{{ $reception->accessories_note ?: 'Tidak ada titipan barang' }}</div>
            </div>
        </div>

        <div class="row text-center mt-5 pt-3">
            <div class="col-6">
                <div>Petugas Penerima,</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold text-decoration-underline">{{ $reception->receiver->name ?? '-' }}</div>
            </div>
            <div class="col-6">
                <div>Pelanggan / Pemilik,</div>
                <div style="height: 60px;"></div>
                <div class="fw-bold text-decoration-underline">{{ $reception->customer->name ?? '-' }}</div>
            </div>
        </div>

        <div class="no-print mt-4 pt-3 border-top text-center">
            <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer me-1"></i> Cetak Dokumen</button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm ms-2">Tutup</button>
        </div>
    </div>
</body>
</html>

