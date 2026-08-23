<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Department;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Category::withCount('items')
            ->with('departments:id,name');

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code_prefix', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $categories = $query->latest()->paginate(10)->withQueryString();

        return inertia('Apps/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Apps/Categories/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'code_prefix'       => 'required|string|max:10|unique:categories,code_prefix|alpha',
            'description'       => 'nullable|string|max:500',
            'department_ids'    => 'nullable|array',
            'department_ids.*'  => 'exists:departments,id',
        ]);

        $category = Category::create([
            'name'        => $request->name,
            'code_prefix' => strtoupper($request->code_prefix),
            'description' => strip_tags($request->description),
        ]);

        $category->departments()->sync($request->department_ids ?? []);

        return to_route('apps.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return inertia('Apps/Categories/Edit', [
            'category'    => $category->load('departments:id'),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);
        $request->validate([
            'name'              => 'required|string|max:255',
            'code_prefix'       => 'required|string|max:10|unique:categories,code_prefix,' . $category->id . '|alpha',
            'description'       => 'nullable|string|max:500',
            'department_ids'    => 'nullable|array',
            'department_ids.*'  => 'exists:departments,id',
        ]);

        $category->update([
            'name'        => $request->name,
            'code_prefix' => strtoupper($request->code_prefix),
            'description' => strip_tags($request->description),
        ]);

        $category->departments()->sync($request->department_ids ?? []);

        return to_route('apps.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        try {
            if ($category->items()->exists()) {
                return back()->withErrors(['error' => 'Kategori tidak bisa dihapus karena masih memiliki material.']);
            }

            $category->departments()->detach();
            $category->delete();

            return to_route('apps.categories.index')->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus kategori.']);
        }
    }
}