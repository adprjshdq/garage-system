@extends('layouts.app')

@section('title', 'Edit Kendaraan — ' . $vehicle->license_plate)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Edit Data Kendaraan</h4>
            <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Batal</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Pemilik Kendaraan (Pelanggan) <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select" required>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id', $vehicle->customer_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }}) &bull; {{ $c->customer_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor Polisi (Plat Motor) <span class="text-danger">*</span></label>
                        <input type="text" name="license_plate" class="form-control text-uppercase font-monospace" value="{{ old('license_plate', $vehicle->license_plate) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Merek Kendaraan <span class="text-danger">*</span></label>
                        <input type="text" name="brand" class="form-control" value="{{ old('brand', $vehicle->brand) }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Model / Tipe Motor <span class="text-danger">*</span></label>
                        <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tahun Perakitan</label>
                        <input type="number" name="year" class="form-control" value="{{ old('year', $vehicle->year) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Warna</label>
                        <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle->color) }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kilometer Terakhir (ODO)</label>
                        <div class="input-group">
                            <input type="number" name="last_mileage" class="form-control" value="{{ old('last_mileage', $vehicle->last_mileage) }}">
                            <span class="input-group-text bg-light text-muted">km</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Nomor Rangka (VIN)</label>
                        <input type="text" name="vin_number" class="form-control font-monospace" value="{{ old('vin_number', $vehicle->vin_number) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Nomor Mesin</label>
                        <input type="text" name="engine_number" class="form-control font-monospace" value="{{ old('engine_number', $vehicle->engine_number) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Catatan Kendaraan</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $vehicle->notes) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Status Kendaraan</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $vehicle->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $vehicle->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash me-1"></i> Hapus Kendaraan
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-mc-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data kendaraan <strong>{{ $vehicle->license_plate }}</strong>?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus Kendaraan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

