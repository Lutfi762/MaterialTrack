<?php

namespace App\Http\Controllers\Apps;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __invoke(Request $request)
    {
        $permissions = Permission::when(request()->q, function($query) {
                $query->where('name', 'like', '%' . request()->q . '%');
            })
            ->latest()
            ->paginate(5);

        return inertia('Apps/Permissions/Index', [
            'permissions' => $permissions
        ]);
    }
}