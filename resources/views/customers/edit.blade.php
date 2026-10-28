@extends('layouts.app')

@section('title', 'Edit Pelanggan — ' . $customer->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">Edit Data Pelanggan</h4>
            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left me-1"></i> Batal</a>
        </div>

        <div class="card card-custom p-4">
            <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alamat Lengkap</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Catatan Khusus</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $customer->notes) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Status Pelanggan</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $customer->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $customer->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash me-1"></i> Hapus Pelanggan
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-mc-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Konfirmasi Penghapusan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus pelanggan <strong>{{ $customer->name }}</strong>?
                <p class="small text-muted mt-2 mb-0">Catatan: Pelanggan yang telah memiliki riwayat servis atau faktur tidak dapat dihapus permanen untuk integritas histori audit.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Hapus Pelanggan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

