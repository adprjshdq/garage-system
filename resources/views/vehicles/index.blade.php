@extends('layouts.app')

@section('title', 'Data Kendaraan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Data Kendaraan Sepeda Motor</h4>
        <p class="text-muted small mb-0">Kelola master nomor polisi, model kendaraan, dan pelacakan kilometer servis.</p>
    </div>
    <a href="{{ route('vehicles.create') }}" class="btn btn-mc-primary">
        <i class="bi bi-plus-circle me-1"></i> Daftarkan Kendaraan
    </a>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('vehicles.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari no. polisi, model, pemilik, atau no. rangka..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="brand" class="form-select">
                <option value="">Semua Merek</option>
                @foreach($brands as $b)
                    <option value="{{ $b }}" {{ $brand === $b ? 'selected' : '' }}>{{ $b }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('vehicles.index') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Nomor Polisi</th>
                    <th>Merek & Model</th>
                    <th>Pemilik (Pelanggan)</th>
                    <th>Tahun & Warna</th>
                    <th>Kilometer Terakhir</th>
                    <th>Riwayat Servis</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $veh)
                    <tr>
                        <td>
                            <span class="badge bg-dark fs-6 px-2 py-1 font-monospace">{{ $veh->license_plate }}</span>
                        </td>
                        <td>
                            <a href="{{ route('vehicles.show', $veh->id) }}" class="fw-bold text-decoration-none">
                                {{ $veh->brand }} {{ $veh->model }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('customers.show', $veh->customer_id) }}" class="text-decoration-none text-secondary">
                                <i class="bi bi-person me-1"></i> {{ $veh->customer->name ?? '-' }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $veh->year ?: '-' }}</div>
                            <small class="text-muted">{{ $veh->color ?: '-' }}</small>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ number_format($veh->last_mileage, 0, ',', '.') }}</span> km
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border">{{ $veh->work_orders_count }} Kali Servis</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('vehicles.show', $veh->id) }}" class="btn btn-light border" title="Detail"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('vehicles.edit', $veh->id) }}" class="btn btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data kendaraan terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vehicles->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $vehicles->links() }}
        </div>
    @endif
</div>
@endsection

