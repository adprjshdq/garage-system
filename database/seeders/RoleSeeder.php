<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'owner',
                'label' => 'Owner',
                'description' => 'Pemilik bengkel dengan akses penuh ke seluruh modul, keuangan, audit log, dan pengaturan sistem.',
            ],
            [
                'name' => 'admin_cashier',
                'label' => 'Admin / Kasir',
                'description' => 'Mengelola pendaftaran pelanggan, penerimaan kendaraan, kasir, invoice, dan penerimaan pembayaran.',
            ],
            [
                'name' => 'service_advisor',
                'label' => 'Service Advisor',
                'description' => 'Menerima kendaraan, diagnosa keluhan, membuat estimasi Work Order, persetujuan pelanggan, dan penugasan mekanik.',
            ],
            [
                'name' => 'mechanic',
                'label' => 'Mekanik',
                'description' => 'Melihat pekerjaan yang ditugaskan, memperbarui progres pengerjaan, mencatat hasil servis, dan pemakaian spare part.',
            ],
            [
                'name' => 'warehouse',
                'label' => 'Gudang',
                'description' => 'Mengelola stok spare part, pengeluaran barang, penerimaan barang dari supplier, dan penyesuaian stok opname.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
