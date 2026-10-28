@extends('layouts.app')

@section('title', 'Detail Spare Part — ' . $sparePart->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold text-dark mb-0">{{ $sparePart->name }}</h4>
            <span class="badge bg-light text-dark border font-monospace">{{ $sparePart->sku }}</span>
            @if($sparePart->isLowStock())
                <span class="badge bg-danger">Stok Menipis</span>
            @endif
        </div>
        <p class="text-muted small mb-0 mt-1">Kategori: <strong>{{ $sparePart->category->name ?? 'Tanpa Kategori' }}</strong> &bull; Lokasi Rak: <strong>{{ $sparePart->rack_location ?: '-' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#adjustModal">
            <i class="bi bi-sliders me-1"></i> Penyesuaian Stok (Opname)
        </button>
        <a href="{{ route('spare-parts.edit', $sparePart->id) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-pencil me-1"></i> Edit Master
        </a>
        <a href="{{ route('spare-parts.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Kartu Informasi Stok & Harga -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <span class="text-muted small fw-semibold text-uppercase">Stok Fisik di Bengkel</span>
            <h3 class="fw-bold my-1 {{ $sparePart->isLowStock() ? 'text-danger' : 'text-success' }}">
                {{ number_format($sparePart->stock_quantity, 0) }} <small class="fs-6 text-muted">{{ $sparePart->unit }}</small>
            </h3>
            <small class="text-muted">Batas Minimum: {{ number_format($sparePart->min_stock, 0) }} {{ $sparePart->unit }}</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <span class="text-muted small fw-semibold text-uppercase">Dialokasikan (WO Aktif)</span>
            <h3 class="fw-bold my-1 text-warning">
                {{ number_format($sparePart->reserved_quantity, 0) }} <small class="fs-6 text-muted">{{ $sparePart->unit }}</small>
            </h3>
            <small class="text-muted">Di-booking untuk pekerjaan servis</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <span class="text-muted small fw-semibold text-uppercase">Stok Siap Pakai / Jual</span>
            <h3 class="fw-bold my-1 text-primary">
                {{ number_format($sparePart->available_stock, 0) }} <small class="fs-6 text-muted">{{ $sparePart->unit }}</small>
            </h3>
            <small class="text-muted">Fisik dikurangi reservasi WO</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <span class="text-muted small fw-semibold text-uppercase">Harga Jual / HPP</span>
            <h4 class="fw-bold my-1 text-dark">Rp{{ number_format($sparePart->selling_price, 0, ',', '.') }}</h4>
            <small class="text-muted">HPP Referensi: Rp{{ number_format($sparePart->reference_cost_price, 0, ',', '.') }}</small>
        </div>
    </div>
</div>

<!-- Riwayat Mutasi / Pergerakan Stok (Stock Movements) -->
<div class="card card-custom p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-primary me-1"></i> Kartu Stok & Audit Mutasi Fisik</h5>
            <small class="text-muted">Setiap penambahan, pengeluaran, pemakaian WO, dan opname tercatat secara kronologis.</small>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu Mutasi</th>
                    <th>Tipe Pergerakan</th>
                    <th>Jumlah Mutasi</th>
                    <th>Saldo Sebelum</th>
                    <th>Saldo Sesudah</th>
                    <th>No. Referensi / Dokumen</th>
                    <th>Petugas</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td>
                            <div>{{ $m->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $m->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            @switch($m->movement_type)
                                @case('in')
                                    <span class="badge bg-success-subtle text-success border">Barang Masuk (PO)</span>
                                    @break
                                @case('out')
                                    <span class="badge bg-danger-subtle text-danger border">Barang Keluar (WO)</span>
                                    @break
                                @case('adjustment')
                                    <span class="badge bg-warning-subtle text-warning border">Penyesuaian (Opname)</span>
                                    @break
                                @case('return')
                                    <span class="badge bg-info-subtle text-info border">Retur</span>
                                    @break
                                @case('initial')
                                    <span class="badge bg-secondary-subtle text-secondary border">Saldo Awal</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark border">{{ $m->movement_type }}</span>
                            @endswitch
                        </td>
                        <td>
                            <span class="fw-bold fs-6 {{ $m->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $m->quantity > 0 ? '+' : '' }}{{ number_format($m->quantity, 0) }}
                            </span>
                        </td>
                        <td>{{ number_format($m->stock_before, 0) }}</td>
                        <td class="fw-bold text-dark">{{ number_format($m->stock_after, 0) }}</td>
                        <td>
                            @if($m->reference_number)
                                <span class="badge bg-light text-dark border font-monospace">{{ $m->reference_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><small>{{ $m->user->name ?? '-' }}</small></td>
                        <td><small class="text-muted">{{ $m->notes }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat mutasi stok untuk barang ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $movements->links() }}
        </div>
    @endif
</div>

<!-- Modal Penyesuaian Stok (Stock Opname) -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('spare-parts.adjust', $sparePart->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-1 text-warning"></i> Penyesuaian Stok Fisik (Stock Opname)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border p-2 mb-3">
                        <div class="small text-muted">Stok Sistem Saat Ini:</div>
                        <div class="fs-4 fw-bold text-primary">{{ number_format($sparePart->stock_quantity, 0) }} {{ $sparePart->unit }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jumlah Stok Fisik Sebenarnya (Hasil Hitung) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="physical_quantity" class="form-control" placeholder="0" min="0" step="1" required>
                            <span class="input-group-text bg-light">{{ $sparePart->unit }}</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Alasan Penyesuaian / Keterangan Opname <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Selisih stock opname akhir bulan, barang rusak/hilang, dll..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-semibold">Simpan Penyesuaian</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

