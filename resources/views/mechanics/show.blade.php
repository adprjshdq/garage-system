@extends('layouts.app')

@section('title', 'Detail Mekanik — ' . $mechanic->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold text-dark mb-0">{{ $mechanic->name }}</h4>
            <span class="badge bg-light text-dark border">{{ $mechanic->code }}</span>
            @if($mechanic->is_active)
                <span class="badge bg-success-subtle text-success border">Aktif</span>
            @else
                <span class="badge bg-danger-subtle text-danger border">Nonaktif</span>
            @endif
        </div>
        <p class="text-muted small mb-0 mt-1">Spesialisasi: <strong>{{ $mechanic->specialization ?: 'Umum' }}</strong> &bull; Telepon: {{ $mechanic->phone }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('mechanics.edit', $mechanic->id) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-pencil me-1"></i> Edit Data
        </a>
        <a href="{{ route('mechanics.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Total Pekerjaan Diselesaikan</span>
                <h3 class="fw-bold my-1 text-success">{{ $totalCompleted }} WO</h3>
                <small class="text-muted">Riwayat pengerjaan selesai</small>
            </div>
            <div class="icon-box bg-success-subtle text-success">
                <i class="bi bi-check2-all"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Pekerjaan Sedang Ditangani</span>
                <h3 class="fw-bold my-1 text-primary">{{ $inProgress }} Motor</h3>
                <small class="text-muted">Sedang dikerjakan saat ini</small>
            </div>
            <div class="icon-box bg-primary-subtle text-primary">
                <i class="bi bi-wrench"></i>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom p-3">
    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-1"></i> Riwayat Pekerjaan Servis</h6>
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>No. WO</th>
                    <th>Kendaraan</th>
                    <th>Pelanggan</th>
                    <th>Peran Mekanik</th>
                    <th>Waktu Mulai</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mechanic->workOrders as $wo)
                    <tr>
                        <td>
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="fw-bold text-decoration-none">
                                {{ $wo->wo_number }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-dark font-monospace">{{ $wo->vehicle->license_plate ?? '-' }}</span>
                            <div class="small">{{ $wo->vehicle->brand ?? '' }} {{ $wo->vehicle->model ?? '' }}</div>
                        </td>
                        <td>{{ $wo->customer->name ?? '-' }}</td>
                        <td>{{ $wo->pivot->role_description ?: 'Mekanik Bertugas' }}</td>
                        <td>{{ $wo->pivot->started_at ? \Carbon\Carbon::parse($wo->pivot->started_at)->format('d/m/Y H:i') : '-' }}</td>
                        <td>{!! $wo->status_badge !!}</td>
                        <td class="text-end">
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat pengerjaan tercatat untuk mekanik ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

