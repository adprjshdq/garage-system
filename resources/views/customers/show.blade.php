@extends('layouts.app')

@section('title', 'Detail Pelanggan — ' . $customer->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold text-dark mb-0">{{ $customer->name }}</h4>
            <span class="badge bg-light text-dark border">{{ $customer->customer_code }}</span>
            @if($customer->is_active)
                <span class="badge bg-success-subtle text-success border">Aktif</span>
            @else
                <span class="badge bg-danger-subtle text-danger border">Nonaktif</span>
            @endif
        </div>
        <p class="text-muted small mb-0 mt-1">Terdaftar sejak {{ $customer->created_at->format('d/m/Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('vehicles.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-mc-accent">
            <i class="bi bi-scooter me-1"></i> Tambah Kendaraan
        </a>
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-pencil me-1"></i> Edit Data
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Info Kontak -->
    <div class="col-md-4">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-1"></i> Informasi Pelanggan</h6>
            <div class="mb-2">
                <small class="text-muted d-block">Nomor Telepon / WA</small>
                <div class="fw-semibold">{{ $customer->phone }}</div>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Email</small>
                <div>{{ $customer->email ?: '-' }}</div>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Alamat</small>
                <div>{{ $customer->address ?: '-' }}</div>
            </div>
            <div>
                <small class="text-muted d-block">Catatan</small>
                <div class="fst-italic text-secondary">{{ $customer->notes ?: '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Keuangan Pelanggan -->
    <div class="col-md-4">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cash-stack text-success me-1"></i> Total Transaksi</h6>
            <div class="mb-3">
                <small class="text-muted d-block">Total Pembayaran Diterima</small>
                <h4 class="fw-bold text-success my-1">Rp{{ number_format($totalSpent, 0, ',', '.') }}</h4>
            </div>
            <div>
                <small class="text-muted d-block">Sisa Tagihan / Piutang</small>
                <h5 class="fw-bold text-danger my-1">Rp{{ number_format($totalOutstanding, 0, ',', '.') }}</h5>
            </div>
        </div>
    </div>

    <!-- Daftar Motor Terdaftar -->
    <div class="col-md-4">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-scooter text-info me-1"></i> Kendaraan ({{ $customer->vehicles->count() }})</h6>
                <a href="{{ route('vehicles.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-link p-0 text-decoration-none">+ Tambah</a>
            </div>
            <div class="list-group list-group-flush small">
                @forelse($customer->vehicles as $veh)
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('vehicles.show', $veh->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $veh->license_plate }}
                            </a>
                            <div class="text-muted">{{ $veh->brand }} {{ $veh->model }} ({{ $veh->color }})</div>
                        </div>
                        <span class="badge bg-light text-secondary border">{{ number_format($veh->last_mileage, 0, ',', '.') }} km</span>
                    </div>
                @empty
                    <div class="text-muted text-center py-3">Belum ada kendaraan terdaftar.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Servis / Work Order -->
<div class="card card-custom p-3 mb-4">
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-1"></i> Riwayat Work Order / Servis</h6>
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>No. WO</th>
                    <th>Kendaraan</th>
                    <th>Kilometer</th>
                    <th>Keluhan</th>
                    <th>Status</th>
                    <th>Total Biaya</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->workOrders as $wo)
                    <tr>
                        <td>
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="fw-semibold text-primary">
                                {{ $wo->wo_number }}
                            </a>
                            <div class="small text-muted">{{ $wo->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td>{{ $wo->vehicle->license_plate ?? '-' }}</td>
                        <td>{{ number_format($wo->mileage, 0, ',', '.') }} km</td>
                        <td><small class="text-truncate d-inline-block" style="max-width: 250px;">{{ $wo->customer_complaint }}</small></td>
                        <td>{!! $wo->status_badge !!}</td>
                        <td>Rp{{ number_format($wo->actual_total_cost > 0 ? $wo->actual_total_cost : $wo->estimated_cost, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3 text-muted">Belum ada riwayat servis untuk pelanggan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

