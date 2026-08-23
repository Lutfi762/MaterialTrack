<?php

namespace App\Http\Controllers\Apps;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::when(request()->q, function ($roles) {
            $roles = $roles->where('name', 'like', '%' . request()->q . '%');
        })->with('permissions')->latest()->paginate(5);

        return inertia('Apps/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        return inertia('Apps/Roles/Create', [
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'        => 'required|unique:roles',
            'permissions' => 'required',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->givePermissionTo($request->permissions);

        return redirect()->route('apps.roles.index');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return inertia('Apps/Roles/Edit', [
            'role'        => $role->load('permissions'),
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->validate($request, [
            'name'        => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('apps.roles.index');
    }

    public function destroy(Role $role)
    {
        // Guard: role utama tidak boleh dihapus
        if ($role->name === 'admin') {
            return back()->withErrors(['error' => 'Role admin adalah role utama sistem dan tidak bisa dihapus.']);
        }

        // Guard: role yang masih dipakai user tidak boleh dihapus
        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => "Role {$role->name} masih dipakai user dan tidak bisa dihapus."]);
        }

        $role->delete();

        return redirect()->route('apps.roles.index');
    }
}