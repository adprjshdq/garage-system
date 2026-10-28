@extends('layouts.app')

@section('title', 'Detail Kendaraan — ' . $vehicle->license_plate)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark fs-5 font-monospace px-3 py-1">{{ $vehicle->license_plate }}</span>
            <h4 class="fw-bold text-dark mb-0">{{ $vehicle->brand }} {{ $vehicle->model }}</h4>
            @if($vehicle->is_active)
                <span class="badge bg-success-subtle text-success border">Aktif</span>
            @else
                <span class="badge bg-danger-subtle text-danger border">Nonaktif</span>
            @endif
        </div>
        <p class="text-muted small mb-0 mt-1">Pemilik: <strong>{{ $vehicle->customer->name ?? '-' }}</strong> &bull; Terdaftar sejak {{ $vehicle->created_at->format('d/m/Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('receptions.create', ['customer_id' => $vehicle->customer_id, 'vehicle_id' => $vehicle->id]) }}" class="btn btn-sm btn-mc-accent">
            <i class="bi bi-box-arrow-in-down-right me-1"></i> Buat Penerimaan Servis
        </a>
        <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-pencil me-1"></i> Edit Data
        </a>
        <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Spesifikasi Motor -->
    <div class="col-md-6">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-1"></i> Spesifikasi Kendaraan</h6>
            <div class="row g-2">
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Merek & Tipe</small>
                    <div class="fw-semibold">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Tahun / Warna</small>
                    <div>{{ $vehicle->year ?: '-' }} / {{ $vehicle->color ?: '-' }}</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Kilometer ODO Terakhir</small>
                    <div class="fw-bold text-primary">{{ number_format($vehicle->last_mileage, 0, ',', '.') }} km</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Frekuensi Servis</small>
                    <div>{{ $vehicle->workOrders->count() }} kali pengerjaan</div>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block">Nomor Rangka (VIN)</small>
                    <div class="font-monospace small">{{ $vehicle->vin_number ?: '-' }}</div>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block">Nomor Mesin</small>
                    <div class="font-monospace small">{{ $vehicle->engine_number ?: '-' }}</div>
                </div>
            </div>
            @if($vehicle->notes)
                <div class="mt-3 pt-2 border-top">
                    <small class="text-muted d-block">Catatan Khusus</small>
                    <div class="fst-italic text-secondary small">{{ $vehicle->notes }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Data Pemilik -->
    <div class="col-md-6">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person text-success me-1"></i> Data Pemilik</h6>
                <a href="{{ route('customers.show', $vehicle->customer_id) }}" class="btn btn-sm btn-link p-0 text-decoration-none">Detail Pelanggan</a>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Nama Pemilik</small>
                <div class="fw-bold text-dark fs-6">{{ $vehicle->customer->name ?? '-' }}</div>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Nomor Kontak / WhatsApp</small>
                <div><i class="bi bi-telephone me-1 text-muted"></i> {{ $vehicle->customer->phone ?? '-' }}</div>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Alamat</small>
                <div class="small">{{ $vehicle->customer->address ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Pengerjaan Servis Motor Ini -->
<div class="card card-custom p-3 mb-4">
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-1"></i> Histori Perawatan & Servis Kendaraan</h6>
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>No. WO</th>
                    <th>Tanggal</th>
                    <th>Kilometer</th>
                    <th>Keluhan & Pekerjaan</th>
                    <th>Mekanik</th>
                    <th>Status</th>
                    <th>Biaya</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicle->workOrders as $wo)
                    <tr>
                        <td>
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="fw-semibold text-primary">
                                {{ $wo->wo_number }}
                            </a>
                        </td>
                        <td>{{ $wo->created_at->format('d/m/Y') }}</td>
                        <td>{{ number_format($wo->mileage, 0, ',', '.') }} km</td>
                        <td>
                            <div class="small fw-semibold text-truncate" style="max-width: 250px;">{{ $wo->customer_complaint }}</div>
                        </td>
                        <td>
                            @foreach($wo->mechanics as $mech)
                                <span class="badge bg-light text-dark border">{{ $mech->name }}</span>
                            @endforeach
                        </td>
                        <td>{!! $wo->status_badge !!}</td>
                        <td>Rp{{ number_format($wo->actual_total_cost > 0 ? $wo->actual_total_cost : $wo->estimated_cost, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3 text-muted">Kendaraan ini belum memiliki histori servis tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

