@extends('layouts.app')

@section('title', 'Penerimaan Kendaraan Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Formulir Penerimaan Kendaraan Masuk</h4>
            <a href="{{ route('receptions.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('receptions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Pelanggan Pemilik <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customerSelect" class="form-select" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" data-vehicles='@json($c->vehicles)' {{ (string)$selectedCustomerId === (string)$c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone }}) &bull; {{ $c->customer_code }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Belum terdaftar? <a href="{{ route('customers.create') }}" target="_blank">Tambah Pelanggan Baru</a></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Sepeda Motor <span class="text-danger">*</span></label>
                        <select name="vehicle_id" id="vehicleSelect" class="form-select" required>
                            <option value="">-- Pilih Motor Milik Pelanggan --</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kilometer Masuk (ODO) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="mileage_in" id="mileageInput" class="form-control" placeholder="0" value="{{ old('mileage_in') }}" required>
                            <span class="input-group-text bg-light text-muted">km</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Estimasi Waktu Selesai (Opsional)</label>
                        <input type="datetime-local" name="estimated_completion_time" class="form-control" value="{{ old('estimated_completion_time') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Keluhan Pelanggan <span class="text-danger">*</span></label>
                    <textarea name="customer_complaint" class="form-control" rows="3" placeholder="Jelaskan detail keluhan, bunyi-bunyi tidak normal, atau servis yang diminta pelanggan..." required>{{ old('customer_complaint') }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kondisi Awal Fisik Kendaraan</label>
                        <textarea name="initial_condition" class="form-control" rows="2" placeholder="Level bensin, lecet body yang ada sebelumnya, spion pecah, dll...">{{ old('initial_condition') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Barang Bawaan / Aksesori yang Dititip</label>
                        <textarea name="accessories_note" class="form-control" rows="2" placeholder="Helm, jas hujan di bagasi, kunci gembok cakram, dll...">{{ old('accessories_note') }}</textarea>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Foto Kondisi Fisik Motor (Opsional)</label>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                    <div class="form-text small">Format gambar JPG/PNG, ukuran maks 4MB.</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('receptions.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-mc-primary"><i class="bi bi-check-lg me-1"></i> Simpan Penerimaan Kendaraan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const customerSelect = document.getElementById('customerSelect');
    const vehicleSelect = document.getElementById('vehicleSelect');
    const mileageInput = document.getElementById('mileageInput');

    function updateVehicleOptions() {
        const selectedOption = customerSelect.options[customerSelect.selectedIndex];
        vehicleSelect.innerHTML = '<option value="">-- Pilih Motor Milik Pelanggan --</option>';

        if (!selectedOption || !selectedOption.value) return;

        const vehicles = JSON.parse(selectedOption.getAttribute('data-vehicles') || '[]');
        vehicles.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = `${v.license_plate} - ${v.brand} ${v.model} (${v.color || 'Standar'})`;
            opt.dataset.mileage = v.last_mileage;
            vehicleSelect.appendChild(opt);
        });

        if (vehicles.length === 1) {
            vehicleSelect.selectedIndex = 1;
            mileageInput.value = vehicles[0].last_mileage;
        }
    }

    customerSelect.addEventListener('change', updateVehicleOptions);
    vehicleSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected && selected.dataset.mileage) {
            mileageInput.value = selected.dataset.mileage;
        }
    });

    // Run on initial load if preselected
    if (customerSelect.value) {
        updateVehicleOptions();
    }
</script>
@endpush

