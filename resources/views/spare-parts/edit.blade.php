@extends('layouts.app')

@section('title', 'Edit Spare Part — ' . $sparePart->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Edit Data Spare Part</h4>
            <a href="{{ route('spare-parts.show', $sparePart->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Batal</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('spare-parts.update', $sparePart->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Kode SKU / Part Number <span class="text-danger">*</span></label>
                        <input type="text" name="sku" class="form-control font-monospace text-uppercase" value="{{ old('sku', $sparePart->sku) }}" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-semibold">Kategori Spare Part</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $sparePart->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Spare Part / Barang <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $sparePart->name) }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', $sparePart->unit) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Harga Beli Referensi (HPP) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="reference_cost_price" class="form-control" value="{{ old('reference_cost_price', (int)$sparePart->reference_cost_price) }}" required min="0" step="1000">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Harga Jual ke Pelanggan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', (int)$sparePart->selling_price) }}" required min="0" step="1000">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Batas Minimum Stok (Alert) <span class="text-danger">*</span></label>
                        <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', (int)$sparePart->min_stock) }}" required min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Lokasi Penyimpanan Rak</label>
                        <input type="text" name="rack_location" class="form-control" value="{{ old('rack_location', $sparePart->rack_location) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Deskripsi / Kompatibilitas Motor</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $sparePart->description) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Status Master Spare Part</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $sparePart->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $sparePart->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('spare-parts.show', $sparePart->id) }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-mc-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

