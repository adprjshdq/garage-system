@extends('layouts.app')

@section('title', 'Buat Work Order Baru')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Buat Work Order (SPK) Baru</h4>
        <p class="text-muted small mb-0">Rancang estimasi jasa dan spare part yang akan dikerjakan pada kendaraan pelanggan.</p>
    </div>
    <a href="{{ route('work-orders.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
</div>

<form action="{{ route('work-orders.store') }}" method="POST" id="woForm">
    @csrf

    @if($reception)
        <input type="hidden" name="reception_id" value="{{ $reception->id }}">
        <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-5 me-2"></i>
            <div>
                Membuat Work Order berdasarkan Penerimaan Kendaraan <strong>{{ $reception->reception_number }}</strong>.
                Data kendaraan, pelanggan, dan keluhan telah diisi secara otomatis.
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <!-- Informasi Dasar -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 h-100">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-badge text-primary me-1"></i> Pelanggan & Motor</h6>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Pelanggan <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerSelect" class="form-select" required {{ $reception ? 'readonly' : '' }}>
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-vehicles='@json($c->vehicles)' {{ ($reception && $reception->customer_id == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }}) &bull; {{ $c->customer_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Sepeda Motor <span class="text-danger">*</span></label>
                    <select name="vehicle_id" id="vehicleSelect" class="form-select" required>
                        @if($reception && $reception->vehicle)
                            <option value="{{ $reception->vehicle_id }}" selected>
                                {{ $reception->vehicle->license_plate }} - {{ $reception->vehicle->brand }} {{ $reception->vehicle->model }}
                            </option>
                        @else
                            <option value="">-- Pilih Motor Milik Pelanggan --</option>
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Kilometer Masuk (ODO) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="mileage" id="mileageInput" class="form-control" value="{{ $reception ? $reception->mileage_in : old('mileage', 0) }}" required>
                        <span class="input-group-text bg-light text-muted">km</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keluhan & Diagnosa -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 h-100">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clipboard-pulse text-info me-1"></i> Diagnosa & Keluhan</h6>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Keluhan Pelanggan <span class="text-danger">*</span></label>
                    <textarea name="customer_complaint" class="form-control" rows="3" required>{{ $reception ? $reception->customer_complaint : old('customer_complaint') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Hasil Pemeriksaan / Diagnosa Awal</label>
                    <textarea name="inspection_result" class="form-control" rows="2" placeholder="Catatan analisa teknis Service Advisor...">{{ old('inspection_result') }}</textarea>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-semibold">Catatan Operasional (Opsional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Catatan tambahan untuk tim mekanik..." value="{{ old('notes') }}">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Rincian Pekerjaan (Jasa & Spare Part) -->
    <div class="card card-custom p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-task text-primary me-1"></i> Rincian Pekerjaan & Estimasi Biaya</h5>
                <small class="text-muted">Pilih paket jasa servis dan komponen suku cadang yang akan dipasang.</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="addServiceRowBtn">
                    <i class="bi bi-wrench me-1"></i> + Tambah Jasa Servis
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" id="addPartRowBtn">
                    <i class="bi bi-box-seam me-1"></i> + Tambah Spare Part
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-custom align-middle mb-0" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Tipe</th>
                        <th>Deskripsi Jasa / Spare Part</th>
                        <th style="width: 110px;">Qty</th>
                        <th style="width: 160px;">Harga Satuan</th>
                        <th style="width: 130px;">Diskon (Rp)</th>
                        <th style="width: 160px;">Subtotal</th>
                        <th style="width: 60px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                    <!-- Dynamic rows inserted here -->
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="5" class="text-end">Total Estimasi Biaya:</td>
                        <td colspan="2" class="text-primary fs-5" id="totalEstimatedText">Rp0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('work-orders.index') }}" class="btn btn-light border">Batal</a>
        <button type="submit" class="btn btn-mc-primary px-4 py-2 fw-semibold">
            <i class="bi bi-check2-circle me-1"></i> Simpan Work Order (Draft)
        </button>
    </div>
</form>

@endsection

@push('scripts')
<!-- Master options JSON for JS builder -->
<script type="application/json" id="servicesData">
    {!! json_encode($services) !!}
</script>
<script type="application/json" id="partsData">
    {!! json_encode($spareParts) !!}
</script>
<script>
    const availableServices = JSON.parse(document.getElementById('servicesData').textContent || '[]');
    const availableParts = JSON.parse(document.getElementById('partsData').textContent || '[]');
    const customerSelect = document.getElementById('customerSelect');
    const vehicleSelect = document.getElementById('vehicleSelect');
    const mileageInput = document.getElementById('mileageInput');

    function updateVehicles() {
        if (!customerSelect || customerSelect.hasAttribute('readonly')) return;
        const opt = customerSelect.options[customerSelect.selectedIndex];
        vehicleSelect.innerHTML = '<option value="">-- Pilih Motor Milik Pelanggan --</option>';
        if (!opt || !opt.value) return;

        const vehicles = JSON.parse(opt.getAttribute('data-vehicles') || '[]');
        vehicles.forEach(v => {
            const o = document.createElement('option');
            o.value = v.id;
            o.textContent = `${v.license_plate} - ${v.brand} ${v.model}`;
            o.dataset.mileage = v.last_mileage;
            vehicleSelect.appendChild(o);
        });
        if (vehicles.length === 1) {
            vehicleSelect.selectedIndex = 1;
            mileageInput.value = vehicles[0].last_mileage;
        }
    }

    customerSelect?.addEventListener('change', updateVehicles);

    let rowIndex = 0;
    const tableBody = document.getElementById('itemsTableBody');

    function addServiceRow(serviceId = null) {
        const tr = document.createElement('tr');
        tr.dataset.index = rowIndex;

        let serviceOptions = '<option value="">-- Pilih Jasa Servis --</option>';
        availableServices.forEach(s => {
            const selected = serviceId == s.id ? 'selected' : '';
            serviceOptions += `<option value="${s.id}" data-price="${s.price}" data-name="${s.name}" ${selected}>[${s.category}] ${s.name} - Rp${Number(s.price).toLocaleString('id-ID')}</option>`;
        });

        tr.innerHTML = `
            <td>
                <span class="badge bg-primary-subtle text-primary border">Jasa Servis</span>
                <input type="hidden" name="items[${rowIndex}][item_type]" value="service">
                <input type="hidden" name="items[${rowIndex}][item_name]" class="item-name-input">
            </td>
            <td>
                <select name="items[${rowIndex}][service_id]" class="form-select service-select" required>
                    ${serviceOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="1" min="1" step="1" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][unit_price]" class="form-control price-input" value="0" min="0" step="1000" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][discount]" class="form-control discount-input" value="0" min="0" step="1000">
            </td>
            <td class="fw-bold line-subtotal text-dark">Rp0</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="bi bi-trash"></i></button>
            </td>
        `;

        tableBody.appendChild(tr);
        bindRowEvents(tr);
        rowIndex++;
        calculateTotal();
    }

    function addPartRow(partId = null) {
        const tr = document.createElement('tr');
        tr.dataset.index = rowIndex;

        let partOptions = '<option value="">-- Pilih Spare Part --</option>';
        availableParts.forEach(p => {
            const selected = partId == p.id ? 'selected' : '';
            partOptions += `<option value="${p.id}" data-price="${p.selling_price}" data-name="${p.name}" ${selected}>${p.sku} - ${p.name} (Stok: ${p.stock_quantity} ${p.unit}) - Rp${Number(p.selling_price).toLocaleString('id-ID')}</option>`;
        });

        tr.innerHTML = `
            <td>
                <span class="badge bg-success-subtle text-success border">Spare Part</span>
                <input type="hidden" name="items[${rowIndex}][item_type]" value="part">
                <input type="hidden" name="items[${rowIndex}][item_name]" class="item-name-input">
            </td>
            <td>
                <select name="items[${rowIndex}][spare_part_id]" class="form-select part-select" required>
                    ${partOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="1" min="1" step="1" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][unit_price]" class="form-control price-input" value="0" min="0" step="1000" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][discount]" class="form-control discount-input" value="0" min="0" step="1000">
            </td>
            <td class="fw-bold line-subtotal text-dark">Rp0</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="bi bi-trash"></i></button>
            </td>
        `;

        tableBody.appendChild(tr);
        bindRowEvents(tr);
        rowIndex++;
        calculateTotal();
    }

    function bindRowEvents(tr) {
        const serviceSelect = tr.querySelector('.service-select');
        const partSelect = tr.querySelector('.part-select');
        const nameInput = tr.querySelector('.item-name-input');
        const priceInput = tr.querySelector('.price-input');
        const qtyInput = tr.querySelector('.qty-input');
        const discountInput = tr.querySelector('.discount-input');
        const removeBtn = tr.querySelector('.remove-row-btn');

        if (serviceSelect) {
            serviceSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    nameInput.value = opt.dataset.name;
                    priceInput.value = opt.dataset.price;
                } else {
                    nameInput.value = '';
                    priceInput.value = 0;
                }
                updateRowSubtotal(tr);
            });
        }

        if (partSelect) {
            partSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    nameInput.value = opt.dataset.name;
                    priceInput.value = opt.dataset.price;
                } else {
                    nameInput.value = '';
                    priceInput.value = 0;
                }
                updateRowSubtotal(tr);
            });
        }

        [qtyInput, priceInput, discountInput].forEach(inp => {
            inp.addEventListener('input', () => updateRowSubtotal(tr));
        });

        removeBtn.addEventListener('click', () => {
            tr.remove();
            calculateTotal();
        });
    }

    function updateRowSubtotal(tr) {
        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        const discount = parseFloat(tr.querySelector('.discount-input').value) || 0;
        const subtotal = Math.max(0, (qty * price) - discount);

        tr.querySelector('.line-subtotal').textContent = 'Rp' + subtotal.toLocaleString('id-ID');
        calculateTotal();
    }

    function calculateTotal() {
        let grandTotal = 0;
        tableBody.querySelectorAll('tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.qty-input')?.value) || 0;
            const price = parseFloat(tr.querySelector('.price-input')?.value) || 0;
            const discount = parseFloat(tr.querySelector('.discount-input')?.value) || 0;
            grandTotal += Math.max(0, (qty * price) - discount);
        });

        document.getElementById('totalEstimatedText').textContent = 'Rp' + grandTotal.toLocaleString('id-ID');
    }

    document.getElementById('addServiceRowBtn').addEventListener('click', () => addServiceRow());
    document.getElementById('addPartRowBtn').addEventListener('click', () => addPartRow());

    // Initial default row
    addServiceRow();
</script>
@endpush

