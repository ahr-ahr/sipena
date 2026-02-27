<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Kelas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();

    $user->fill([
        'email' => $request->email,
    ]);

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    $user->save();

    if ($user->tipe_user === \App\Enums\UserType::SISWA) {

        $user->siswaProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama'     => $request->nama,
                'nis'      => $request->nis,
                'kelas_id' => $request->kelas_id,
            ]
        );

    } elseif ($user->tipe_user === \App\Enums\UserType::PEGAWAI) {

        $user->pegawaiProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama' => $request->nama,
                'nip'  => $request->nip,
            ]
        );
    }

    return Redirect::route('profile.edit')
        ->with('status', 'profile-updated');
}

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
