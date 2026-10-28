@extends('layouts.app')

@section('title', 'Detail Penerimaan — ' . $reception->reception_number)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold text-dark mb-0">{{ $reception->reception_number }}</h4>
            <span class="badge bg-primary">{{ strtoupper(str_replace('_', ' ', $reception->status)) }}</span>
        </div>
        <p class="text-muted small mb-0 mt-1">Diterima oleh: <strong>{{ $reception->receiver->name ?? '-' }}</strong> pada {{ $reception->reception_time->format('d/m/Y H:i') }} WIB</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('receptions.print', $reception->id) }}" target="_blank" class="btn btn-sm btn-light border">
            <i class="bi bi-printer me-1"></i> Cetak Formulir
        </a>
        @if(!$reception->workOrder && $reception->status !== 'cancelled')
            <a href="{{ route('work-orders.create', ['reception_id' => $reception->id]) }}" class="btn btn-sm btn-mc-primary">
                <i class="bi bi-file-earmark-plus me-1"></i> Buat Work Order (SPK)
            </a>
        @endif
        <a href="{{ route('receptions.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Info Kendaraan & Pelanggan -->
    <div class="col-md-6">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-scooter text-primary me-1"></i> Data Motor & Pemilik</h6>
            <div class="row g-2">
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Nomor Polisi</small>
                    <span class="badge bg-dark font-monospace fs-6">{{ $reception->vehicle->license_plate ?? '-' }}</span>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Kilometer Masuk</small>
                    <div class="fw-bold text-primary">{{ number_format($reception->mileage_in, 0, ',', '.') }} km</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Merek & Model</small>
                    <div class="fw-semibold">{{ $reception->vehicle->brand ?? '-' }} {{ $reception->vehicle->model ?? '' }}</div>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">Tahun / Warna</small>
                    <div>{{ $reception->vehicle->year ?: '-' }} / {{ $reception->vehicle->color ?: '-' }}</div>
                </div>
                <div class="col-12 mt-2 pt-2 border-top">
                    <small class="text-muted d-block">Nama Pelanggan</small>
                    <div class="fw-bold">{{ $reception->customer->name ?? '-' }} (<i class="bi bi-telephone"></i> {{ $reception->customer->phone ?? '-' }})</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kondisi & Aksesori -->
    <div class="col-md-6">
        <div class="card card-custom p-3 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clipboard-check text-info me-1"></i> Catatan Fisik & Penitipan</h6>
            <div class="mb-2">
                <small class="text-muted d-block">Keluhan Pelanggan</small>
                <div class="p-2 bg-light rounded text-dark">{{ $reception->customer_complaint }}</div>
            </div>
            <div class="mb-2">
                <small class="text-muted d-block">Kondisi Awal Motor</small>
                <div>{{ $reception->initial_condition ?: 'Tidak ada catatan kerusakan fisik khusus.' }}</div>
            </div>
            <div>
                <small class="text-muted d-block">Aksesori / Barang Dititip</small>
                <div>{{ $reception->accessories_note ?: 'Tidak ada barang yang dititipkan.' }}</div>
            </div>
        </div>
    </div>
</div>

@if($reception->workOrder)
    <div class="card card-custom p-3 border-start border-4 border-primary">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-primary mb-1">Work Order Terkait</span>
                <h5 class="fw-bold text-dark mb-0">{{ $reception->workOrder->wo_number }}</h5>
                <small class="text-muted">Status: {!! $reception->workOrder->status_badge !!}</small>
            </div>
            <a href="{{ route('work-orders.show', $reception->workOrder->id) }}" class="btn btn-mc-primary">
                Buka Work Order <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
@endif
@endsection

