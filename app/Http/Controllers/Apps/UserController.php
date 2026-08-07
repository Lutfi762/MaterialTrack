<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['department', 'roles'])
            ->when(request('q'), function ($query) {
                $query->where('name', 'like', '%' . request('q') . '%')
                    ->orWhere('email', 'like', '%' . request('q') . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Apps/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return inertia('Apps/Users/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'roles'       => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'roles'         => 'required|array|min:1',
            'roles.*'       => 'exists:roles,name',
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => bcrypt($validated['password']),
            'department_id' => $validated['department_id'] ?? null,
        ]);

        $user->syncRoles($validated['roles']);

        return to_route('apps.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return inertia('Apps/Users/Edit', [
            'user'        => $user->load('roles'),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'roles'       => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'      => 'nullable|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'roles'         => 'required|array|min:1',
            'roles.*'       => 'exists:roles,name',
        ]);

        $user->update([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'department_id' => $validated['department_id'] ?? null,
            ...(!empty($validated['password']) ? ['password' => bcrypt($validated['password'])] : []),
        ]);

        $user->syncRoles($validated['roles']);

        return to_route('apps.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return to_route('apps.users.index')->with('success', 'User berhasil dihapus.');
    }
}