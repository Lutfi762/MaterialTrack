<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Pic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PicController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Pic::with('department')->latest();

        if ($user->hasRole('department_user')) {
            $query->where('department_id', $user->department_id);
        }

        $pics = $query->paginate(10);

        return Inertia::render('Apps/Pics/Index', [
            'pics' => $pics,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $departments = $user->hasRole('department_user')
            ? Department::where('id', $user->department_id)->get(['id', 'name'])
            : Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Apps/Pics/Create', [
            'departments'        => $departments,
            'lockedDepartmentId' => $user->hasRole('department_user') ? $user->department_id : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'nik'           => 'nullable|string|max:50|unique:pics,nik',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        if ($user->hasRole('department_user') && $request->department_id != $user->department_id) {
            abort(403, 'Anda hanya bisa menambahkan PIC untuk departemen Anda sendiri.');
        }

        Pic::create($request->only('name', 'nik', 'phone', 'email', 'department_id'));

        return to_route('apps.pics.index')->with('success', 'PIC berhasil ditambahkan.');
    }

    public function edit(Pic $pic)
    {
        $this->authorizePicAccess($pic);

        $user = auth()->user();

        $departments = $user->hasRole('department_user')
            ? Department::where('id', $user->department_id)->get(['id', 'name'])
            : Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Apps/Pics/Edit', [
            'pic'         => $pic->load('department'),
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, Pic $pic)
    {
        $this->authorizePicAccess($pic);

        $user = auth()->user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'nik'           => 'nullable|string|max:50|unique:pics,nik,' . $pic->id,
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        if ($user->hasRole('department_user') && $request->department_id != $user->department_id) {
            abort(403, 'Anda hanya bisa mengelola PIC untuk departemen Anda sendiri.');
        }

        $pic->update($request->only('name', 'nik', 'phone', 'email', 'department_id'));

        return to_route('apps.pics.index')->with('success', 'PIC berhasil diperbarui.');
    }

    public function destroy(Pic $pic)
    {
        $pic->delete();

        return to_route('apps.pics.index')->with('success', 'PIC berhasil dihapus.');
    }

    private function authorizePicAccess(Pic $pic): void
    {
        $user = auth()->user();

        if ($user->hasRole('department_user') && $pic->department_id != $user->department_id) {
            abort(403, 'Anda tidak memiliki akses ke PIC departemen lain.');
        }
    }
}