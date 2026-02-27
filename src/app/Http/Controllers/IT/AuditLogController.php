<?php

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view-audit-log');
        $query = AuditLog::with('user');

        // FILTER: USER
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // FILTER: ACTION
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // FILTER: DATE RANGE
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if (! $request->expectsJson()) {
            $users = User::orderBy('email')->get();
            return view('it.audit.index', compact('users'));
        }

        return response()->json([
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }
}
