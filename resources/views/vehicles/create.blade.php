@extends('layouts.app')

@section('title', 'Daftarkan Kendaraan Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Daftarkan Kendaraan Baru</h4>
            <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('vehicles.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Pemilik Kendaraan (Pelanggan) <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select select2" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (string)$selectedCustomerId === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }}) &bull; {{ $c->customer_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor Polisi (Plat Motor) <span class="text-danger">*</span></label>
                        <input type="text" name="license_plate" class="form-control text-uppercase font-monospace" placeholder="Contoh: B 1234 ABC" value="{{ old('license_plate') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Merek Kendaraan <span class="text-danger">*</span></label>
                        <input type="text" name="brand" class="form-control" placeholder="Contoh: Honda, Yamaha, Suzuki, Kawasaki, Vespa" value="{{ old('brand', 'Honda') }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Model / Tipe Motor <span class="text-danger">*</span></label>
                        <input type="text" name="model" class="form-control" placeholder="Contoh: Vario 160 CBS, NMAX 155, Beat FI" value="{{ old('model') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tahun Perakitan</label>
                        <input type="number" name="year" class="form-control" placeholder="2023" value="{{ old('year', date('Y')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Warna</label>
                        <input type="text" name="color" class="form-control" placeholder="Hitam, Merah, dll" value="{{ old('color') }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kilometer Terakhir (ODO)</label>
                        <div class="input-group">
                            <input type="number" name="last_mileage" class="form-control" placeholder="0" value="{{ old('last_mileage', 0) }}">
                            <span class="input-group-text bg-light text-muted">km</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Nomor Rangka (VIN)</label>
                        <input type="text" name="vin_number" class="form-control font-monospace" placeholder="Opsional" value="{{ old('vin_number') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Nomor Mesin</label>
                        <input type="text" name="engine_number" class="form-control font-monospace" placeholder="Opsional" value="{{ old('engine_number') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Catatan Kendaraan (Opsional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan modifikasi atau riwayat penyakit bawaan...">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-mc-primary"><i class="bi bi-check-lg me-1"></i> Simpan Kendaraan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

