@extends('layouts.app')

@section('title', 'Katalog Jasa Servis')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Katalog Jasa Servis</h4>
        <p class="text-muted small mb-0">Kelola daftar tarif standar jasa pengerjaan, estimasi durasi, dan kategori servis.</p>
    </div>
    <a href="{{ route('services.create') }}" class="btn btn-mc-primary">
        <i class="bi bi-plus-circle me-1"></i> Tambah Jasa Servis
    </a>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('services.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama atau kode jasa..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $c)
                    <option value="{{ $c }}" {{ $category === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('services.index') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Jasa Servis</th>
                    <th>Kategori</th>
                    <th>Deskripsi Pekerjaan</th>
                    <th>Estimasi Waktu</th>
                    <th>Tarif Standar</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $srv)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $srv->code }}</span></td>
                        <td><span class="fw-bold text-dark">{{ $srv->name }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary border">{{ $srv->category }}</span></td>
                        <td><small class="text-truncate d-inline-block text-muted" style="max-width: 280px;">{{ $srv->description ?: '-' }}</small></td>
                        <td><i class="bi bi-stopwatch text-muted me-1"></i> {{ $srv->estimated_duration_minutes }} menit</td>
                        <td class="fw-bold text-success fs-6">Rp{{ number_format($srv->price, 0, ',', '.') }}</td>
                        <td>
                            @if($srv->is_active)
                                <span class="badge bg-success-subtle text-success border">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('services.edit', $srv->id) }}" class="btn btn-sm btn-light border" title="Edit"><i class="bi bi-pencil"></i> Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada data jasa servis yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($services->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $services->links() }}
        </div>
    @endif
</div>
@endsection

