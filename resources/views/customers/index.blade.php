@extends('layouts.app')

@section('title', 'Data Pelanggan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Data Pelanggan</h4>
        <p class="text-muted small mb-0">Kelola riwayat data pemilik kendaraan, kontak, dan riwayat servis.</p>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-mc-primary">
        <i class="bi bi-person-plus me-1"></i> Tambah Pelanggan
    </a>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('customers.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, kode, no. telepon, atau email..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('customers.index') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Pelanggan</th>
                    <th>No. Telepon / Kontak</th>
                    <th>Alamat</th>
                    <th>Kendaraan</th>
                    <th>Riwayat Servis</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $cust)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $cust->customer_code }}</span></td>
                        <td>
                            <a href="{{ route('customers.show', $cust->id) }}" class="fw-bold text-decoration-none">
                                {{ $cust->name }}
                            </a>
                        </td>
                        <td>
                            <div><i class="bi bi-telephone me-1 text-muted"></i> {{ $cust->phone }}</div>
                            @if($cust->email)
                                <small class="text-muted"><i class="bi bi-envelope me-1"></i> {{ $cust->email }}</small>
                            @endif
                        </td>
                        <td>
                            <small class="text-truncate d-inline-block" style="max-width: 180px;">{{ $cust->address ?: '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info border">{{ $cust->vehicles_count }} Motor</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border">{{ $cust->work_orders_count }} WO</span>
                        </td>
                        <td>
                            @if($cust->is_active)
                                <span class="badge bg-success-subtle text-success border">Aktif</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customers.show', $cust->id) }}" class="btn btn-light border" title="Detail"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('customers.edit', $cust->id) }}" class="btn btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada data pelanggan yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection

