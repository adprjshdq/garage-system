<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $module = $request->input('module');
        $userId = $request->input('user_id');

        $query = AuditLog::with('user');

        if ($search) {
            $query->where('summary', 'like', "%{$search}%");
        }

        if ($module) {
            $query->where('module', $module);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();
        $modules = AuditLog::select('module')->distinct()->pluck('module');
        $users = User::orderBy('name')->get();

        return view('audit-logs.index', compact('logs', 'modules', 'users', 'search', 'module', 'userId'));
    }
}
