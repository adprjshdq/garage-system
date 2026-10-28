<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DocumentNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MechanicController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $query = Mechanic::with('user')->withCount([
            'workOrders as total_jobs',
            'workOrders as active_jobs' => fn ($q) => $q->where('status', 'in_progress'),
            'workOrders as completed_jobs' => fn ($q) => $q->whereIn('status', ['completed', 'invoiced', 'closed']),
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $mechanics = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('mechanics.index', compact('mechanics', 'search'));
    }

    public function create(): View
    {
        $users = User::whereDoesntHave('mechanic')
            ->whereHas('roles', fn ($q) => $q->where('name', 'mechanic'))
            ->get();

        return view('mechanics.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'exists:users,id', 'unique:mechanics,user_id'],
        ]);

        $validated['code'] = $this->docService->generate('MECH');
        $validated['is_active'] = true;

        $mechanic = Mechanic::create($validated);

        AuditService::log('created', 'Mechanic', $mechanic->id, "Menambahkan data mekanik baru {$mechanic->name} ({$mechanic->code})");

        return redirect()->route('mechanics.show', $mechanic->id)->with('success', 'Mekanik berhasil didaftarkan!');
    }

    public function show(Mechanic $mechanic): View
    {
        $mechanic->load([
            'user',
            'workOrders' => fn ($q) => $q->with(['customer', 'vehicle'])->latest()->take(20),
        ]);

        $totalCompleted = $mechanic->workOrders()->whereIn('status', ['completed', 'invoiced', 'closed'])->count();
        $inProgress = $mechanic->workOrders()->where('status', 'in_progress')->count();

        return view('mechanics.show', compact('mechanic', 'totalCompleted', 'inProgress'));
    }

    public function edit(Mechanic $mechanic): View
    {
        $users = User::where(function ($q) use ($mechanic) {
            $q->whereDoesntHave('mechanic')->orWhere('id', $mechanic->user_id);
        })->get();

        return view('mechanics.edit', compact('mechanic', 'users'));
    }

    public function update(Request $request, Mechanic $mechanic): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'exists:users,id', 'unique:mechanics,user_id,'.$mechanic->id],
            'is_active' => ['required', 'boolean'],
        ]);

        $old = $mechanic->toArray();
        $mechanic->update($validated);

        AuditService::log('updated', 'Mechanic', $mechanic->id, "Memperbarui data mekanik {$mechanic->name}", $old, $mechanic->toArray());

        return redirect()->route('mechanics.show', $mechanic->id)->with('success', 'Data mekanik berhasil diperbarui!');
    }

    public function destroy(Mechanic $mechanic): RedirectResponse
    {
        if ($mechanic->workOrders()->exists()) {
            return back()->with('error', 'Mekanik ini memiliki riwayat pekerjaan servis dan tidak dapat dihapus permanen. Ubah status menjadi nonaktif.');
        }

        $mechanic->delete();
        AuditService::log('deleted', 'Mechanic', $mechanic->id, "Menghapus data mekanik {$mechanic->name}");

        return redirect()->route('mechanics.index')->with('success', 'Mekanik berhasil dihapus!');
    }
}
