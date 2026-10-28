@extends('layouts.app')

@section('title', 'Inventaris Spare Part')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Inventaris Spare Part & Suku Cadang</h4>
        <p class="text-muted small mb-0">Kelola master suku cadang, pelacakan stok fisik, reservasi WO, dan batas stok minimum.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('spare-parts.movements') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-right me-1"></i> Pergerakan Stok
        </a>
        <a href="{{ route('spare-parts.categories') }}" class="btn btn-outline-primary">
            <i class="bi bi-tags me-1"></i> Kategori
        </a>
        <a href="{{ route('spare-parts.create') }}" class="btn btn-mc-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Spare Part
        </a>
    </div>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('spare-parts.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, SKU, atau lokasi rak..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check pt-2">
                <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="lowStockCheck" {{ $lowStockOnly ? 'checked' : '' }}>
                <label class="form-check-label small fw-semibold text-danger" for="lowStockCheck">
                    Hanya Stok Menipis
                </label>
            </div>
        </div>
        <div class="col-md-2 d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('spare-parts.index') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Harga Beli (HPP)</th>
                    <th>Harga Jual</th>
                    <th>Stok Fisik</th>
                    <th>Dialokasikan (WO)</th>
                    <th>Tersedia</th>
                    <th>Lokasi Rak</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($spareParts as $part)
                    <tr class="{{ $part->isLowStock() ? 'table-warning' : '' }}">
                        <td><span class="badge bg-light text-dark border font-monospace">{{ $part->sku }}</span></td>
                        <td>
                            <a href="{{ route('spare-parts.show', $part->id) }}" class="fw-bold text-decoration-none">
                                {{ $part->name }}
                            </a>
                            @if($part->isLowStock())
                                <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Stok Rendah</span>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary-subtle text-secondary border">{{ $part->category->name ?? 'Tanpa Kategori' }}</span></td>
                        <td>Rp{{ number_format($part->reference_cost_price, 0, ',', '.') }}</td>
                        <td class="fw-bold text-dark">Rp{{ number_format($part->selling_price, 0, ',', '.') }}</td>
                        <td>
                            <span class="fw-bold fs-6 {{ $part->isLowStock() ? 'text-danger' : 'text-success' }}">
                                {{ number_format($part->stock_quantity, 0) }}
                            </span>
                            <small class="text-muted">{{ $part->unit }}</small>
                        </td>
                        <td>
                            @if($part->reserved_quantity > 0)
                                <span class="badge bg-warning text-dark">{{ number_format($part->reserved_quantity, 0) }} {{ $part->unit }}</span>
                            @else
                                <span class="text-muted small">0</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold">{{ number_format($part->available_stock, 0) }}</span> {{ $part->unit }}
                        </td>
                        <td>
                            <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $part->rack_location ?: '-' }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('spare-parts.show', $part->id) }}" class="btn btn-light border" title="Riwayat & Opname"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('spare-parts.edit', $part->id) }}" class="btn btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">Tidak ada data spare part ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($spareParts->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $spareParts->links() }}
        </div>
    @endif
</div>
@endsection

