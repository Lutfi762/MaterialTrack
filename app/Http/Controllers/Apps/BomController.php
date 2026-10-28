<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BomController extends Controller {
    public function index(Request $request) {
        $query = Bom::with('item');
        if ($request->search) $query->where('name', 'like', "%{$request->search}%");
        return Inertia::render('Apps/Boms/Index', [
            'boms' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search')
        ]);
    }
    public function create() {
        return Inertia::render('Apps/Boms/Create', ['items' => Item::all()]);
    }
    public function store(Request $request) {
        $v = $request->validate([
            'item_id' => 'required|exists:items,id',
            'code' => 'required|unique:boms',
            'name' => 'required',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01'
        ]);
        $bom = Bom::create($request->only('item_id', 'code', 'name'));
        $bom->items()->createMany($v['items']);
        return to_route('apps.boms.index')->with('success', 'BOM created');
    }
    public function destroy(Bom $bom) {
        $bom->delete();
        return back()->with('success', 'BOM deleted');
    }
}