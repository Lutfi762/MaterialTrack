<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    const UNIT_OPTIONS = ['PCS', 'Meter', 'Liter', 'Kg', 'Box', 'Roll', 'Set', 'Unit'];

    public function index()
    {
        $user = auth()->user();

        $query = Item::with(['category.departments', 'location']);

        // department_user hanya boleh lihat item dari kategori yang
        // di-assign ke departemennya sendiri.
        if ($user->hasRole('department_user')) {
            $query->whereHas('category.departments', function ($q) use ($user) {
                $q->where('departments.id', $user->department_id);
            });
        }

        // Filter bertingkat: pilih department dulu, baru kategori
        if (request('department_id')) {
            $query->whereHas('category.departments', function ($q) {
                $q->where('departments.id', request('department_id'));
            });
        }

        if (request('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        $items = $query
            ->when(request('q'), fn($q) => $q->where('name', 'like', '%' . request('q') . '%')
                ->orWhere('code', 'like', '%' . request('q') . '%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Daftar department untuk dropdown filter
        $departmentsQuery = Department::orderBy('name');
        if ($user->hasRole('department_user')) {
            $departmentsQuery->where('id', $user->department_id);
        }
        $departments = $departmentsQuery->get(['id', 'name']);

        // Daftar kategori — kalau department_id dipilih di filter, cuma
        // tampilkan kategori yang di-assign ke department itu.
        $categoriesQuery = Category::orderBy('name');
        if ($user->hasRole('department_user')) {
            $categoriesQuery->whereHas('departments', fn($q) => $q->where('departments.id', $user->department_id));
        } elseif (request('department_id')) {
            $categoriesQuery->whereHas('departments', fn($q) => $q->where('departments.id', request('department_id')));
        }
        $categories = $categoriesQuery->get(['id', 'name']);

        return inertia('Apps/Items/Index', [
            'items'       => $items,
            'departments' => $departments,
            'categories'  => $categories,
            'filters'     => request()->only(['department_id', 'category_id', 'q']),
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $categoriesQuery = Category::orderBy('name');
        if ($user->hasRole('department_user')) {
            $categoriesQuery->whereHas('departments', fn($q) => $q->where('departments.id', $user->department_id));
        }

        return inertia('Apps/Items/Create', [
        'departments' => \App\Models\Department::orderBy('name')->get(['id', 'name']),
        'categories'  => Category::with('departments:id')->orderBy('name')->get(['id', 'name', 'code_prefix'])
            ->map(fn ($cat) => [
                'id'             => $cat->id,
                'name'           => $cat->name,
                'code_prefix'    => $cat->code_prefix,
                'department_ids' => $cat->departments->pluck('id'),
            ]),
        'locations'   => Location::orderBy('name')->get(['id', 'name']),
        'unitOptions' => self::UNIT_OPTIONS,
    ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'location_id'   => 'nullable|exists:locations,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'unit'          => 'required|in:' . implode(',', self::UNIT_OPTIONS),
            'min_stock'     => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
            'image'         => 'nullable|image|max:2048',
        ]);

        $category = Category::findOrFail($validated['category_id']);

        $prefix = $category->code_prefix ?? 'GEN';
        $lastNumber = Item::where('category_id', $category->id)
            ->where('code', 'like', $prefix . '-%')
            ->count();
        $validated['code'] = $prefix . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        Item::create($validated);

        return to_route('apps.items.index')->with('success', "Material berhasil ditambahkan dengan kode {$validated['code']}.");
    }

    public function edit(Item $item)
    {
          return inertia('Apps/Items/Edit', [
        'item'        => $item->load('category'),
        'departments' => \App\Models\Department::orderBy('name')->get(['id', 'name']),
        'categories'  => Category::with('departments:id')->orderBy('name')->get(['id', 'name', 'code_prefix'])
            ->map(fn ($cat) => [
                'id'             => $cat->id,
                'name'           => $cat->name,
                'code_prefix'    => $cat->code_prefix,
                'department_ids' => $cat->departments->pluck('id'),
            ]),
        'locations'   => Location::orderBy('name')->get(['id', 'name']),
        'unitOptions' => self::UNIT_OPTIONS,
    ]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit'        => 'required|in:' . implode(',', self::UNIT_OPTIONS),
            'min_stock'   => 'required|integer|min:0',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($validated['category_id'] != $item->category_id) {
            return back()->withErrors([
                'category_id' => 'Kategori tidak bisa diubah setelah material dibuat (kode material sudah terikat ke kategori awal).',
            ])->withInput();
        }

        if ($request->hasFile('image')) {
            if ($item->getRawOriginal('image')) {
                Storage::disk('public')->delete($item->getRawOriginal('image'));
            }
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($validated);

        return to_route('apps.items.index')->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        if ($item->getRawOriginal('image')) {
            Storage::disk('public')->delete($item->getRawOriginal('image'));
        }

        $item->delete();

        return to_route('apps.items.index')->with('success', 'Material berhasil dihapus.');
    }
}