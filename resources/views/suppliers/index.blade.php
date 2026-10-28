@extends('layouts.app')

@section('title', 'Data Supplier')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Data Supplier & Vendor</h4>
        <p class="text-muted small mb-0">Kelola master distributor spare part, informasi kontak, dan riwayat pesanan pembelian.</p>
    </div>
    <a href="{{ route('suppliers.create') }}" class="btn btn-mc-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Supplier
    </a>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('suppliers.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama supplier, kode, no. hp..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Cari</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Supplier</th>
                    <th>Kontak / Telepon</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th>Jumlah PO</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $sup)
                    <tr>
                        <td><span class="badge bg-light text-dark border font-monospace">{{ $sup->code }}</span></td>
                        <td>
                            <a href="{{ route('suppliers.show', $sup->id) }}" class="fw-bold text-decoration-none">
                                {{ $sup->name }}
                            </a>
                        </td>
                        <td><i class="bi bi-telephone text-muted me-1"></i> {{ $sup->phone }}</td>
                        <td>{{ $sup->email ?: '-' }}</td>
                        <td><small class="text-truncate d-inline-block" style="max-width: 200px;">{{ $sup->address ?: '-' }}</small></td>
                        <td><span class="badge bg-primary-subtle text-primary border">{{ $sup->purchase_orders_count }} Pesanan</span></td>
                        <td>
                            @if($sup->is_active)
                                <span class="badge bg-success-subtle text-success border">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('suppliers.show', $sup->id) }}" class="btn btn-light border" title="Detail"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('suppliers.edit', $sup->id) }}" class="btn btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada data supplier.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $suppliers->links() }}
        </div>
    @endif
</div>
@endsection

