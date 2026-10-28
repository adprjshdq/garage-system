@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Tambah Pelanggan Baru</h4>
            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email (Opsional)</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@contoh.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alamat Lengkap</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Alamat rumah / domisili pelanggan">{{ old('address') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Catatan Khusus (Opsional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan preferensi pelanggan...">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-mc-primary"><i class="bi bi-check-lg me-1"></i> Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

