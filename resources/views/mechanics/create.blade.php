@extends('layouts.app')

@section('title', 'Tambah Mekanik Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Tambah Mekanik Baru</h4>
            <a href="{{ route('mechanics.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('mechanics.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Lengkap Teknisi <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Doni Irawan" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Spesialisasi / Keahlian</label>
                    <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}" placeholder="Contoh: CVT & Matic, Mesin 2-Tak/4-Tak, Kelistrikan & Injeksi">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Tautkan ke Akun Login Pengguna (Opsional)</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- Tidak Ada / Mekanik Tanpa Akses Akun --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    <div class="form-text small">Jika ditautkan, mekanik dapat login untuk melihat pekerjaan yang ditugaskan ke dirinya.</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('mechanics.index') }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-mc-primary"><i class="bi bi-check-lg me-1"></i> Simpan Mekanik</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

