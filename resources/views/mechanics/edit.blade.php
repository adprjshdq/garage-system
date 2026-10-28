@extends('layouts.app')

@section('title', 'Edit Mekanik — ' . $mechanic->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Edit Data Mekanik</h4>
            <a href="{{ route('mechanics.show', $mechanic->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Batal</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('mechanics.update', $mechanic->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Lengkap Teknisi <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $mechanic->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $mechanic->phone) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Spesialisasi / Keahlian</label>
                    <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $mechanic->specialization) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tautkan ke Akun Login Pengguna</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- Tidak Ada / Mekanik Tanpa Akses Akun --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id', $mechanic->user_id) == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Status Mekanik</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $mechanic->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $mechanic->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('mechanics.show', $mechanic->id) }}" class="btn btn-light border">Batal</a>
                    <button type="submit" class="btn btn-mc-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

