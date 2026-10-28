@extends('layouts.app')

@section('title', 'Log Pergerakan Stok Spare Part')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Log Pergerakan Stok (Audit Persediaan)</h4>
        <p class="text-muted small mb-0">Riwayat seluruh transaksi mutasi keluar-masuk barang, pemakaian servis, retur, dan opname.</p>
    </div>
    <a href="{{ route('spare-parts.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali ke Stok</a>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('spare-parts.movements') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama barang atau SKU..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="type" class="form-select">
                <option value="">Semua Jenis Pergerakan</option>
                <option value="in" {{ $type === 'in' ? 'selected' : '' }}>Barang Masuk (Penerimaan PO)</option>
                <option value="out" {{ $type === 'out' ? 'selected' : '' }}>Barang Keluar (Pemakaian WO)</option>
                <option value="adjustment" {{ $type === 'adjustment' ? 'selected' : '' }}>Penyesuaian (Stock Opname)</option>
                <option value="return" {{ $type === 'return' ? 'selected' : '' }}>Retur Pembelian / Pemakaian</option>
                <option value="initial" {{ $type === 'initial' ? 'selected' : '' }}>Saldo Awal</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('spare-parts.movements') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu Mutasi</th>
                    <th>Spare Part / SKU</th>
                    <th>Tipe Mutasi</th>
                    <th>Jumlah</th>
                    <th>Saldo Sebelum</th>
                    <th>Saldo Sesudah</th>
                    <th>No. Referensi Dokumen</th>
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
                            <a href="{{ route('spare-parts.show', $m->spare_part_id) }}" class="fw-bold text-decoration-none">
                                {{ $m->sparePart->name ?? '-' }}
                            </a>
                            <div class="small text-muted font-monospace">{{ $m->sparePart->sku ?? '' }}</div>
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
                        <td colspan="9" class="text-center py-4 text-muted">Belum ada riwayat pergerakan stok yang cocok.</td>
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
@endsection

