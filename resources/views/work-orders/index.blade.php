@extends('layouts.app')

@section('title', 'Perintah Kerja (Work Order)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Perintah Kerja (Work Order / SPK)</h4>
        <p class="text-muted small mb-0">Pusat kontrol operasional servis bengkel, penugasan mekanik, dan pelacakan pengerjaan.</p>
    </div>
    <a href="{{ route('work-orders.create') }}" class="btn btn-mc-primary">
        <i class="bi bi-plus-circle me-1"></i> Buat Work Order Baru
    </a>
</div>

<!-- Filter Box -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('work-orders.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari no. WO, plat motor, atau pelanggan..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua Status WO</option>
                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="waiting_approval" {{ $status === 'waiting_approval' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                <option value="waiting_parts" {{ $status === 'waiting_parts' ? 'selected' : '' }}>Menunggu Spare Part</option>
                <option value="final_inspection" {{ $status === 'final_inspection' ? 'selected' : '' }}>Pemeriksaan Akhir</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="invoiced" {{ $status === 'invoiced' ? 'selected' : '' }}>Ditagihkan</option>
                <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Ditutup</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="mechanic_id" class="form-select">
                <option value="">Semua Mekanik</option>
                @foreach($mechanics as $m)
                    <option value="{{ $m->id }}" {{ (string)$mechanicId === (string)$m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('work-orders.index') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>No. WO</th>
                    <th>Waktu Dibuat</th>
                    <th>Kendaraan / Motor</th>
                    <th>Pelanggan</th>
                    <th>Mekanik Bertugas</th>
                    <th>Status</th>
                    <th>Total Biaya</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workOrders as $wo)
                    <tr>
                        <td>
                            <a href="{{ route('work-orders.show', $wo->id) }}" class="fw-bold text-decoration-none">
                                {{ $wo->wo_number }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $wo->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $wo->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <span class="badge bg-dark font-monospace">{{ $wo->vehicle->license_plate ?? '-' }}</span>
                            <div class="small fw-semibold mt-1">{{ $wo->vehicle->brand ?? '' }} {{ $wo->vehicle->model ?? '' }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $wo->customer->name ?? '-' }}</div>
                            <small class="text-muted">{{ $wo->customer->phone ?? '' }}</small>
                        </td>
                        <td>
                            @forelse($wo->mechanics as $mech)
                                <span class="badge bg-light text-dark border">{{ $mech->name }}</span>
                            @empty
                                <span class="text-muted small fst-italic">Belum ditugaskan</span>
                            @endforelse
                        </td>
                        <td>{!! $wo->status_badge !!}</td>
                        <td>
                            <span class="fw-bold">Rp{{ number_format($wo->actual_total_cost > 0 ? $wo->actual_total_cost : $wo->estimated_cost, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-light border" title="Kelola / Lihat WO">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <a href="{{ route('work-orders.print', $wo->id) }}" target="_blank" class="btn btn-light border" title="Cetak SPK">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada Work Order yang sesuai dengan pencarian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($workOrders->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $workOrders->links() }}
        </div>
    @endif
</div>
@endsection

