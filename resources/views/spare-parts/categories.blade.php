@extends('layouts.app')

@section('title', 'Kategori Spare Part')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Kategori Spare Part</h4>
        <p class="text-muted small mb-0">Kelola pengelompokan suku cadang berdasarkan komponen motor.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('spare-parts.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali ke Stok</a>
    </div>
</div>

<div class="row g-4">
    <!-- Form Tambah Kategori -->
    <div class="col-lg-4">
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle text-primary me-1"></i> Tambah Kategori Baru</h6>
            <form action="{{ route('spare-parts.categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Kode Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control text-uppercase" placeholder="Contoh: CAT-BAN" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Ban Luar & Dalam" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Penjelasan kategori..."></textarea>
                </div>
                <button type="submit" class="btn btn-mc-primary w-100">Simpan Kategori</button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Kategori -->
    <div class="col-lg-8">
        <div class="card card-custom p-3">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Part</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $cat->code }}</span></td>
                                <td><span class="fw-bold text-dark">{{ $cat->name }}</span></td>
                                <td><small class="text-muted">{{ $cat->description ?: '-' }}</small></td>
                                <td><span class="badge bg-primary-subtle text-primary border">{{ $cat->spare_parts_count }} Item</span></td>
                                <td class="text-end">
                                    <form action="{{ route('spare-parts.categories.destroy', $cat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada kategori yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

