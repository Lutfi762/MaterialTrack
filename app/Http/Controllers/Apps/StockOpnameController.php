<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Exports\StockOpnamesExport;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\StockOpname;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $opnames = StockOpname::with(['item.category', 'user'])
            ->where('period', $period)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $availablePeriods = StockOpname::select('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period');

        return inertia('Apps/StockOpnames/Index', [
            'opnames'          => $opnames,
            'availablePeriods' => $availablePeriods,
            'currentPeriod'    => $period,
        ]);
    }

    public function create(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $items = Item::with('category')->orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock', 'category_id']);

        $alreadyDone = StockOpname::where('period', $period)->pluck('item_id')->toArray();

        return inertia('Apps/StockOpnames/Create', [
            'items'        => $items,
            'period'       => $period,
            'alreadyDone'  => $alreadyDone,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period'                     => 'required|date_format:Y-m',
            'date'                       => 'required|date',
            'entries'                    => 'required|array|min:1',
            'entries.*.item_id'          => 'required|exists:items,id',
            'entries.*.physical_stock'   => 'required|integer|min:0',
            'entries.*.notes'            => 'nullable|string',
        ]);

        $user = auth()->user();
        $count = 0;

        DB::transaction(function () use ($validated, $user, &$count) {
            foreach ($validated['entries'] as $entry) {
                $exists = StockOpname::where('item_id', $entry['item_id'])
                    ->where('period', $validated['period'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $item = Item::findOrFail($entry['item_id']);
                $systemStock = $item->current_stock;
                $physicalStock = $entry['physical_stock'];
                $difference = $physicalStock - $systemStock;

                $refNo = 'OPN-' . str_replace('-', '', $validated['period']) . '-' . str_pad(
                    StockOpname::where('period', $validated['period'])->count() + 1, 4, '0', STR_PAD_LEFT
                );

                StockOpname::create([
                    'reference_no'   => $refNo,
                    'period'         => $validated['period'],
                    'date'           => $validated['date'],
                    'item_id'        => $item->id,
                    'user_id'        => $user->id,
                    'system_stock'   => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference'     => $difference,
                    'notes'          => $entry['notes'] ?? null,
                ]);

                if ($difference !== 0) {
                    $item->update(['current_stock' => $physicalStock]);

                    StockMovement::create([
                        'item_id'       => $item->id,
                        'user_id'       => $user->id,
                        'type'          => 'adjustment',
                        'qty'           => abs($difference),
                        'stock_before'  => $systemStock,
                        'balance_after' => $physicalStock,
                        'ref_type'      => StockOpname::class,
                        'ref_id'        => $item->id,
                        'notes'         => 'Stock Opname ' . $validated['period']
                            . ($difference > 0 ? ' (surplus)' : ' (defisit)'),
                    ]);
                }

                $count++;
            }
        });

        return to_route('apps.stock-opnames.index', ['period' => $validated['period']])
            ->with('success', "{$count} item berhasil dicatat dalam stock opname periode {$validated['period']}.");
    }

    public function show(StockOpname $stockOpname)
    {
        return inertia('Apps/StockOpnames/Show', [
            'opname' => $stockOpname->load(['item.category', 'user']),
        ]);
    }

    public function downloadPdf(StockOpname $stockOpname)
    {
        $pdf = Pdf::loadView('pdf.stock-opname', [
            'opname' => $stockOpname->load(['item.category', 'user']),
        ]);

        return $pdf->download('StockOpname-' . $stockOpname->reference_no . '.pdf');
    }

    public function downloadAllPdf(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $opnames = StockOpname::with(['item.category', 'user'])
            ->where('period', $period)
            ->orderBy('reference_no')
            ->get();

        $pdf = Pdf::loadView('pdf.stock-opname-batch', [
            'opnames' => $opnames,
            'period'  => $period,
        ]);

        return $pdf->download('StockOpname-Rekap-' . str_replace('-', '', $period) . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $query = StockOpname::query()->where('period', $period)->latest();

        if ($request->filled('category_id')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        return Excel::download(
            new StockOpnamesExport($query),
            'MaterialTrack-StockOpname-' . str_replace('-', '', $period) . '-' . now()->format('His') . '.xlsx'
        );
    }
}