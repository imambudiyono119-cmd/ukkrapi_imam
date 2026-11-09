<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    private const MEMBER_ROLES = ['PIC Ruang', 'Guru', 'Organisasi', 'Guru/Organisasi'];

    private const ASSIGNABLE_MEMBER_ROLES = ['PIC Ruang', 'Guru', 'Organisasi'];

    public function index(): View
    {
        return view('master-data.index', [
            'members' => User::query()
                ->with('role')
                ->whereHas('role', fn ($query) => $query->whereIn('role_name', self::MEMBER_ROLES))
                ->orderBy('fullname')
                ->get(),
            'roles' => Role::query()
                ->whereIn('role_name', self::ASSIGNABLE_MEMBER_ROLES)
                ->orderBy('role_name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $roleIds = Role::query()
            ->whereIn('role_name', self::ASSIGNABLE_MEMBER_ROLES)
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'regex:/^62[0-9]{9,15}$/'],
            'role_id' => ['required', 'integer', Rule::in($roleIds)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create($validated);

        return redirect()->route('master-data.index')->with('success', 'Akun anggota berhasil ditambahkan.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);

        $roleIds = Role::query()
            ->whereIn('role_name', self::ASSIGNABLE_MEMBER_ROLES)
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'role_id' => ['required', 'integer', Rule::in($roleIds)],
        ]);

        $user->role_id = $validated['role_id'];
        $user->save();

        return back()->with('success', 'Peran anggota berhasil diperbarui.');
    }

    public function updateEmail(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->email = $validated['email'];
        $user->save();

        return back()->with('success', 'Email pemulihan berhasil diperbarui.');
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = $validated['password'];
        $user->save();

        return back()->with('success', 'Kata sandi anggota berhasil diatur ulang.');
    }
}
