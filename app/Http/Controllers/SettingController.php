<?php

namespace App\Http\Controllers;

use App\Models\WorkshopSetting;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = WorkshopSetting::all()->pluck('value', 'key')->toArray();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'workshop_name' => ['required', 'string', 'max:255'],
            'workshop_tagline' => ['nullable', 'string', 'max:255'],
            'workshop_phone' => ['required', 'string', 'max:50'],
            'workshop_email' => ['nullable', 'email', 'max:100'],
            'workshop_address' => ['required', 'string'],
            'workshop_hours' => ['nullable', 'string', 'max:100'],
            'prefix_wo' => ['required', 'string', 'max:10'],
            'prefix_invoice' => ['required', 'string', 'max:10'],
            'prefix_reception' => ['required', 'string', 'max:10'],
            'prefix_po' => ['required', 'string', 'max:10'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($data as $key => $value) {
            WorkshopSetting::set($key, (string) $value);
        }

        AuditService::log('updated', 'Settings', null, 'Memperbarui konfigurasi informasi dan nomor dokumen bengkel');

        return back()->with('success', 'Pengaturan bengkel berhasil diperbarui!');
    }
}
