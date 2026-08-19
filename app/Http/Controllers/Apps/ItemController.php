<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Imports\ItemImport;
use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\CacheService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    use AuthorizesRequests;

    const UNIT_OPTIONS = ['PCS', 'Meter', 'Liter', 'Kg', 'Box', 'Roll', 'Set', 'Unit'];

    public function index()
    {
        $user = auth()->user();

        $departmentId = $user->hasRole('department_user') ? $user->department_id : request('department_id');
        $categoryId = request('category_id');
        $status = request('status');
        $search = request('q');

        // Use CacheService untuk mendapatkan items list
        $page = Paginator::resolveCurrentPage();
        $cachedData = CacheService::getItemsList($departmentId, $categoryId, $status, $page, $search);

        // Paginate the cached results
        $items = $this->paginateCollection(collect($cachedData['items']), 10);

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
            $categoriesQuery->whereHas('departments', fn ($q) => $q->where('departments.id', $user->department_id));
        } elseif (request('department_id')) {
            $categoriesQuery->whereHas('departments', fn ($q) => $q->where('departments.id', request('department_id')));
        }
        $categories = $categoriesQuery->get(['id', 'name']);

        return inertia('Apps/Items/Index', [
            'items' => $items,
            'departments' => $departments,
            'categories' => $categories,
            'filters' => request()->only(['department_id', 'category_id', 'status', 'q']),
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $categoriesQuery = Category::orderBy('name');
        if ($user->hasRole('department_user')) {
            $categoriesQuery->whereHas('departments', fn ($q) => $q->where('departments.id', $user->department_id));
        }

        return inertia('Apps/Items/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => Category::with('departments:id')->orderBy('name')->get(['id', 'name', 'code_prefix'])
                ->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'code_prefix' => $cat->code_prefix,
                    'department_ids' => $cat->departments->pluck('id'),
                ]),
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'unitOptions' => self::UNIT_OPTIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'unit' => 'required|in:'.implode(',', self::UNIT_OPTIONS),
            'unit_alt' => 'nullable|string|max:20',
            'conversion_factor' => 'nullable|numeric|min:0.01|max:999999.99',
            'min_stock' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100',
        ]);

        // Sanitize description
        if (isset($validated['description'])) {
            $validated['description'] = strip_tags($validated['description']);
        }

        // Clear alt unit fields if not provided
        if (empty($validated['unit_alt'])) {
            $validated['unit_alt'] = null;
            $validated['conversion_factor'] = null;
        }

        $category = Category::findOrFail($validated['category_id']);

        $prefix = $category->code_prefix ?? 'GEN';
        $lastNumber = Item::where('category_id', $category->id)
            ->where('code', 'like', $prefix.'-%')
            ->count();
        $validated['code'] = $prefix.'-'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        if ($request->hasFile('image')) {
            try {
                $validated['image'] = $request->file('image')->store('items', 'public');
            } catch (\Exception $e) {
                return back()->withErrors(['image' => 'Gagal mengunggah gambar. Silakan coba lagi.'])->withInput();
            }
        }

        // Handle multiple photos upload
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                try {
                    $photos[] = $photo->store('items', 'public');
                } catch (\Exception $e) {
                    continue;
                }
            }
            $validated['photos'] = $photos;
        }

        try {
            Item::create($validated);
        } catch (\Exception $e) {
            // Cleanup uploaded image if database insert fails
            if (isset($validated['image'])) {
                Storage::disk('public')->delete($validated['image']);
            }

            return back()->withErrors(['error' => 'Gagal menyimpan data material.'])->withInput();
        }

        return to_route('apps.items.index')->with('success', "Material berhasil ditambahkan dengan kode {$validated['code']}.");
    }

    public function edit(Item $item)
    {
        $this->authorize('update', $item);

        return inertia('Apps/Items/Edit', [
            'item' => $item->load('category'),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'categories' => Category::with('departments:id')->orderBy('name')->get(['id', 'name', 'code_prefix'])
                ->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'code_prefix' => $cat->code_prefix,
                    'department_ids' => $cat->departments->pluck('id'),
                ]),
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'unitOptions' => self::UNIT_OPTIONS,
        ]);
    }

    public function show(Item $item)
    {
        $this->authorize('view', $item);

        $item->load('category', 'location');

        return inertia('Apps/Items/Show', [
            'item' => $item,
        ]);
    }

    public function history(Item $item)
    {
        $this->authorize('view', $item);

        $movements = StockMovement::with('user')
            ->where('item_id', $item->id)
            ->latest()
            ->paginate(30);

        return inertia('Apps/Items/History', [
            'item' => $item,
            'movements' => $movements,
        ]);
    }

    public function lookupByCode(Request $request)
    {
        $code = $request->input('code');
        if (! $code) {
            return response()->json(['success' => false, 'message' => 'Kode material tidak diberikan.'], 400);
        }

        $item = Item::with('category', 'location')
            ->where('code', $code)
            ->first();

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Material tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'unit' => 'required|in:'.implode(',', self::UNIT_OPTIONS),
            'unit_alt' => 'nullable|string|max:20',
            'conversion_factor' => 'nullable|numeric|min:0.01|max:999999.99',
            'min_stock' => 'required|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100',
        ]);

        // Sanitize description
        if (isset($validated['description'])) {
            $validated['description'] = strip_tags($validated['description']);
        }

        // Clear alt unit fields if not provided
        if (empty($validated['unit_alt'])) {
            $validated['unit_alt'] = null;
            $validated['conversion_factor'] = null;
        }

        if ($validated['category_id'] != $item->category_id) {
            return back()->withErrors([
                'category_id' => 'Kategori tidak bisa diubah setelah material dibuat (kode material sudah terikat ke kategori awal).',
            ])->withInput();
        }

        if ($request->hasFile('image')) {
            try {
                if ($item->getRawOriginal('image')) {
                    Storage::disk('public')->delete($item->getRawOriginal('image'));
                }
                $validated['image'] = $request->file('image')->store('items', 'public');
            } catch (\Exception $e) {
                return back()->withErrors(['image' => 'Gagal mengunggah gambar. Silakan coba lagi.'])->withInput();
            }
        }

        // Handle multiple photos upload
        if ($request->hasFile('photos')) {
            // Delete old photos
            if ($item->photos) {
                foreach ($item->photos as $oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                try {
                    $photos[] = $photo->store('items', 'public');
                } catch (\Exception $e) {
                    continue;
                }
            }
            $validated['photos'] = $photos;
        }

        // Handle photo deletion (existing photo removed by user)
        if ($request->input('removed_photos')) {
            $removed = is_array($request->input('removed_photos')) ? $request->input('removed_photos') : [$request->input('removed_photos')];
            foreach ($removed as $path) {
                Storage::disk('public')->delete($path);
            }
            $remainingPhotos = array_diff($item->photos ?? [], $removed);
            $validated['photos'] = array_values($remainingPhotos);
        }

        try {
            $item->update($validated);
        } catch (\Exception $e) {
            // Cleanup new uploaded image if update fails
            if (isset($validated['image']) && $validated['image'] !== $item->getRawOriginal('image')) {
                Storage::disk('public')->delete($validated['image']);
            }

            return back()->withErrors(['error' => 'Gagal memperbarui data material.'])->withInput();
        }

        return to_route('apps.items.index')->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);

        try {
            if ($item->stockIns()->exists() || $item->stockOuts()->exists() || $item->stockMovements()->exists()) {
                return back()->withErrors(['error' => 'Material tidak bisa dihapus karena sudah memiliki riwayat transaksi.']);
            }

            if ($item->getRawOriginal('image')) {
                Storage::disk('public')->delete($item->getRawOriginal('image'));
            }

            $item->delete();

            return to_route('apps.items.index')->with('success', 'Material berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus material.']);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            Excel::import(new ItemImport, $request->file('file'));

            return to_route('apps.items.index')->with('success', 'Import material berhasil diproses.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal import: '.$e->getMessage()]);
        }
    }

    /**
     * Paginate a collection manually
     *
     * @param  Collection  $collection
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    private function paginateCollection($collection, $perPage = 10)
    {
        $page = Paginator::resolveCurrentPage();
        $total = $collection->count();

        $paginator = new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return $paginator->withQueryString();
    }
}
