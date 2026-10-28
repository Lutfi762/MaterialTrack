<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\QualityControl;
use App\Models\Item;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QualityControlController extends Controller {
    public function index(Request $request) {
        $query = QualityControl::with('item');
        if ($request->search) $query->where('qc_number', 'like', "%{$request->search}%");
        return Inertia::render('Apps/QualityControls/Index', [
            'qcs' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search')
        ]);
    }
    public function create() {
        return Inertia::render('Apps/QualityControls/Create', [
            'items' => Item::all()
        ]);
    }
    public function store(Request $request) {
        $v = $request->validate([
            'reference_number' => 'nullable|string',
            'item_id' => 'required|exists:items,id',
            'qty_inspected' => 'required|integer|min:1',
            'qty_passed' => 'required|integer|min:0|lte:qty_inspected',
            'inspector_name' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);
        $v['qty_rejected'] = $v['qty_inspected'] - $v['qty_passed'];
        $v['status'] = $v['qty_rejected'] == 0 ? 'passed' : ($v['qty_passed'] == 0 ? 'failed' : 'partial');
        DB::transaction(function () use ($v) {
            $v['qc_number'] = DocumentNumber::next('quality_controls', 'QC');
            QualityControl::create($v);
        });
        return to_route('apps.quality-controls.index')->with('success', 'QC recorded');
    }
    public function destroy(QualityControl $qc) {
        // Model routing uses explicit parameter name matching typehint, but to be safe:
        $qc->delete();
        return back()->with('success', 'QC deleted');
    }
}