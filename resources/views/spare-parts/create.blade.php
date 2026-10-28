@extends('layouts.app')

@section('title', 'Tambah Spare Part Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Tambah Spare Part Baru</h4>
            <a href="{{ route('spare-parts.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('spare-parts.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Kode SKU / Part Number <span class="text-danger">*</span></label>
                        <input type="text" name="sku" class="form-control font-monospace text-uppercase" placeholder="Contoh: PRT-OLI-003" value="{{ old('sku') }}" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-semibold">Kategori Spare Part</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Spare Part / Barang <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Kampas Rem Depan Honda Beat ESP" value="{{ old('name') }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                        <input type="text" name="unit" class="form-control" placeholder="Pcs, Botol, Set, Unit" value="{{ old('unit', 'Pcs') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Harga Beli Referensi (HPP) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="reference_cost_price" class="form-control" placeholder="0" value="{{ old('reference_cost_price', 0) }}" required min="0" step="1000">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Harga Jual ke Pelanggan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="selling_price" class="form-control" placeholder="0" value="{{ old('selling_price', 0) }}" required min="0" step="1000">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Saldo Stok Fisik Awal <span class="text-danger">*</span></label>
                        <input type="number" name="stock_quantity" class="form-control" placeholder="0" value="{{ old('stock_quantity', 0) }}" required min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Batas Minimum Stok (Alert) <span class="text-danger">*</span></label>
                        <input type="number" name="min_stock" class="form-control" placeholder="5" value="{{ old('min_stock', 5) }}" required min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Lokasi Penyimpanan Rak</label>
                        <input type="text" name="rack_location" class="form-control" placeholder="Contoh: Rak B-2" value="{{ old('rack_location') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Deskripsi / Kompatibilitas Motor</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Kompatibel untuk Honda Beat 2018-2023, Scoopy FI...">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('spare-parts.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-mc-primary"><i class="bi bi-check-lg me-1"></i> Simpan Spare Part</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

