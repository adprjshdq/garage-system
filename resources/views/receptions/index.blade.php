@extends('layouts.app')

@section('title', 'Penerimaan Kendaraan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Penerimaan Kendaraan Masuk</h4>
        <p class="text-muted small mb-0">Catat kondisi awal, barang bawaan, keluhan pelanggan, dan kilometer saat motor masuk bengkel.</p>
    </div>
    <a href="{{ route('receptions.create') }}" class="btn btn-mc-primary">
        <i class="bi bi-plus-circle me-1"></i> Terima Motor Masuk
    </a>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('receptions.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari no. penerimaan, plat motor, atau nama pelanggan..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua Status Penerimaan</option>
                <option value="waiting_inspection" {{ $status === 'waiting_inspection' ? 'selected' : '' }}>Menunggu Pemeriksaan</option>
                <option value="in_inspection" {{ $status === 'in_inspection' ? 'selected' : '' }}>Dalam Pemeriksaan</option>
                <option value="processed" {{ $status === 'processed' ? 'selected' : '' }}>Diproses ke WO</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('receptions.index') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>No. Penerimaan</th>
                    <th>Waktu Masuk</th>
                    <th>Plat Motor & Model</th>
                    <th>Pelanggan</th>
                    <th>Kilometer Masuk</th>
                    <th>Keluhan Utama</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receptions as $rec)
                    <tr>
                        <td>
                            <a href="{{ route('receptions.show', $rec->id) }}" class="fw-bold text-decoration-none">
                                {{ $rec->reception_number }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $rec->reception_time->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $rec->reception_time->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <span class="badge bg-dark font-monospace">{{ $rec->vehicle->license_plate ?? '-' }}</span>
                            <div class="small fw-semibold mt-1">{{ $rec->vehicle->brand ?? '' }} {{ $rec->vehicle->model ?? '' }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $rec->customer->name ?? '-' }}</div>
                            <small class="text-muted">{{ $rec->customer->phone ?? '' }}</small>
                        </td>
                        <td>{{ number_format($rec->mileage_in, 0, ',', '.') }} km</td>
                        <td>
                            <small class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $rec->customer_complaint }}">
                                {{ $rec->customer_complaint }}
                            </small>
                        </td>
                        <td>
                            @switch($rec->status)
                                @case('waiting_inspection')
                                    <span class="badge bg-warning text-dark">Menunggu Pemeriksaan</span>
                                    @break
                                @case('processed')
                                    <span class="badge bg-primary">Diproses ke WO</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success">Selesai</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">Dibatalkan</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $rec->status }}</span>
                            @endswitch
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('receptions.show', $rec->id) }}" class="btn btn-light border" title="Lihat"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('receptions.print', $rec->id) }}" target="_blank" class="btn btn-light border" title="Cetak Form"><i class="bi bi-printer"></i></a>
                                @if(!$rec->workOrder && $rec->status !== 'cancelled')
                                    <a href="{{ route('work-orders.create', ['reception_id' => $rec->id]) }}" class="btn btn-sm btn-mc-primary" title="Proses ke WO">
                                        <i class="bi bi-file-earmark-plus"></i> Buat WO
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada penerimaan kendaraan tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($receptions->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $receptions->links() }}
        </div>
    @endif
</div>
@endsection

