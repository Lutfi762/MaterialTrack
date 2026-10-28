<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\Bom;
use App\Models\Item;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductionOrderController extends Controller {
    public function index(Request $request) {
        return Inertia::render('Apps/ProductionOrders/Index', [
            'orders' => ProductionOrder::with(['bom', 'item'])->paginate(15)
        ]);
    }
    public function create() {
        return Inertia::render('Apps/ProductionOrders/Create', [
            'boms' => Bom::with('item')->get()
        ]);
    }
    public function store(Request $request) {
        $v = $request->validate([
            'bom_id' => 'required|exists:boms,id',
            'qty_planned' => 'required|integer|min:1',
            'start_date' => 'required|date'
        ]);
        $bom = Bom::find($v['bom_id']);
        DB::transaction(function () use ($bom, $v) {
            ProductionOrder::create([
                'order_number' => DocumentNumber::next('production_orders', 'PO'),
                'bom_id' => $bom->id,
                'item_id' => $bom->item_id,
                'qty_planned' => $v['qty_planned'],
                'start_date' => $v['start_date'],
                'status' => 'planned'
            ]);
        });
        return to_route('apps.production-orders.index')->with('success', 'Order created');
    }
    public function complete(ProductionOrder $productionOrder) {
        if ($productionOrder->status === 'completed') return back();
        DB::transaction(function() use ($productionOrder) {
            $bom = $productionOrder->bom()->with('items')->first();
            foreach($bom->items as $bi) {
                $raw = Item::find($bi->item_id);
                $raw->current_stock -= ($bi->quantity * $productionOrder->qty_planned);
                $raw->save();
            }
            $fg = Item::find($productionOrder->item_id);
            $fg->current_stock += $productionOrder->qty_planned;
            $fg->save();
            $productionOrder->update(['status' => 'completed', 'qty_produced' => $productionOrder->qty_planned, 'end_date' => now()]);
        });
        return back()->with('success', 'Order completed.');
    }
}