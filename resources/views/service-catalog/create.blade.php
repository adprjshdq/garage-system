@extends('layouts.app')

@section('title', 'Tambah Jasa Servis')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Tambah Jasa Servis Baru</h4>
            <a href="{{ route('services.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('services.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kode Jasa <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase" placeholder="Contoh: SRV-007" value="{{ old('code') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Kategori Servis <span class="text-danger">*</span></label>
                        <input type="text" name="category" class="form-control" list="categoryOptions" placeholder="Pilih atau ketik kategori..." value="{{ old('category') }}" required>
                        <datalist id="categoryOptions">
                            @foreach($categories as $c)
                                <option value="{{ $c }}">
                            @endforeach
                            <option value="Servis Berkala">
                            <option value="Transmisi CVT">
                            <option value="Pelumasan">
                            <option value="Pengereman">
                            <option value="Kelistrikan">
                            <option value="Mesin Berat">
                        </datalist>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Jasa Servis <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Kuras & Ganti Minyak Rem ABS" value="{{ old('name') }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tarif Standar (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="price" class="form-control" placeholder="0" value="{{ old('price') }}" required min="0" step="1000">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Estimasi Durasi Pengerjaan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="estimated_duration_minutes" class="form-control" placeholder="30" value="{{ old('estimated_duration_minutes', 30) }}" required min="5">
                            <span class="input-group-text bg-light">menit</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Deskripsi Cakupan Servis</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Rincian prosedur pekerjaan yang termasuk dalam jasa ini...">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('services.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-mc-primary"><i class="bi bi-check-lg me-1"></i> Simpan Jasa Servis</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

