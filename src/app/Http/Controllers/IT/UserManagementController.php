<?php

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UpdateUserRoleRequest;
use Illuminate\Support\Facades\Gate;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class UserManagementController extends Controller
{
    public function __construct()
    {
        Gate::authorize('manage-users');
    }

    public function index(Request $request)
    {
        $query = User::with(['siswaProfile', 'pegawaiProfile']);

        // SEARCH
        if ($request->filled('q')) {
            $q = $request->q;

            $query->where(function ($qq) use ($q) {
                $qq->where('email', 'like', "%{$q}%")
                   ->orWhereHas('siswaProfile', fn ($s) =>
                        $s->where('nama', 'like', "%{$q}%")
                   )
                   ->orWhereHas('pegawaiProfile', fn ($p) =>
                        $p->where('nama', 'like', "%{$q}%")
                   );
            });
        }

        // FILTER TIPE
        if ($request->filled('tipe')) {
            $query->where('tipe_user', $request->tipe);
        }

        // SORT
        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        $query->orderBy($sort, $dir);

        $users = $query->paginate(15)->withQueryString();

        /**
         * =============================
         * HYBRID RESPONSE
         * =============================
         */
        if (! $request->expectsJson()) {
            return view('it.users.index');
        }

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        Gate::authorize('manage-users');

        if (auth()->id() === $user->id) {
            abort(403);
        }

        $user->update([
            'tipe_user' => $request->tipe_user,
        ]);

        AuditLogger::log(
            action: 'UPDATE_ROLE',
            targetType: 'User',
            targetId: $user->id,
            description: "Role user {$user->email} diubah dari {$oldRole} ke {$user->tipe_user}"
        );

        if (! $request->expectsJson()) {
            return back()->with('success', 'Role user diperbarui');
        }

        return response()->json([
            'message' => 'Role user diperbarui',
            'data' => [
                'id' => $user->id,
                'tipe_user' => $user->tipe_user,
            ],
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        if (auth()->id() === $user->id) {
            abort(403);
        }

        AuditLogger::log(
            action: 'DELETE_USER',
            targetType: 'User',
            targetId: $user->id,
            description: "User {$user->email} dihapus"
        );

        $user->delete();

        if (! $request->expectsJson()) {
            return back()->with('success', 'User berhasil dihapus');
        }

        return response()->json([
            'message' => 'User berhasil dihapus',
            'id' => $user->id,
        ]);
    }
}
