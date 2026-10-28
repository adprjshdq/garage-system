@extends('layouts.app')

@section('title', 'Data Mekanik')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Data Mekanik & Teknisi</h4>
        <p class="text-muted small mb-0">Kelola master mekanik bengkel, spesialisasi keahlian, dan pemantauan pekerjaan aktif.</p>
    </div>
    <a href="{{ route('mechanics.create') }}" class="btn btn-mc-primary">
        <i class="bi bi-person-plus me-1"></i> Tambah Mekanik
    </a>
</div>

<div class="card card-custom p-3 mb-4">
    <form action="{{ route('mechanics.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, kode, keahlian, no. hp..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-light border"><i class="bi bi-funnel me-1"></i> Cari</button>
            <a href="{{ route('mechanics.index') }}" class="btn btn-light border text-muted">Reset</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Mekanik</th>
                    <th>No. Kontak</th>
                    <th>Keahlian / Spesialisasi</th>
                    <th>Pekerjaan Aktif</th>
                    <th>Pekerjaan Selesai</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mechanics as $mech)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $mech->code }}</span></td>
                        <td>
                            <a href="{{ route('mechanics.show', $mech->id) }}" class="fw-bold text-decoration-none">
                                {{ $mech->name }}
                            </a>
                        </td>
                        <td><i class="bi bi-telephone me-1 text-muted"></i> {{ $mech->phone }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border">{{ $mech->specialization ?: 'Umum' }}</span>
                        </td>
                        <td>
                            @if($mech->active_jobs > 0)
                                <span class="badge bg-primary">{{ $mech->active_jobs }} Motor</span>
                            @else
                                <span class="text-muted small">Tersedia</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold text-success">{{ $mech->completed_jobs }} WO</span>
                        </td>
                        <td>
                            @if($mech->is_active)
                                <span class="badge bg-success-subtle text-success border">Aktif</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('mechanics.show', $mech->id) }}" class="btn btn-light border" title="Detail"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('mechanics.edit', $mech->id) }}" class="btn btn-light border" title="Edit"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada data mekanik ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($mechanics->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $mechanics->links() }}
        </div>
    @endif
</div>
@endsection

