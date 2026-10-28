<?php

namespace Database\Seeders;

use App\Models\WorkshopSetting;
use Illuminate\Database\Seeder;

class WorkshopSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'workshop_name' => 'MotoCare Workshop & Service Center',
            'workshop_tagline' => 'Solusi Terpercaya Perawatan dan Servis Sepeda Motor Anda',
            'workshop_phone' => '0812-9876-5432',
            'workshop_email' => 'admin@motocare.id',
            'workshop_address' => 'Jl. Otomotif Raya No. 88, Kebayoran Baru, Jakarta Selatan',
            'workshop_hours' => 'Senin - Sabtu: 08.00 - 17.00 WIB, Minggu: 09.00 - 15.00 WIB',
            'prefix_wo' => 'WO',
            'prefix_invoice' => 'INV',
            'prefix_reception' => 'REC',
            'prefix_po' => 'PO',
            'default_tax_rate' => '0',
            'timezone' => 'Asia/Jakarta',
        ];

        foreach ($settings as $key => $value) {
            WorkshopSetting::set($key, $value);
        }
    }
}
