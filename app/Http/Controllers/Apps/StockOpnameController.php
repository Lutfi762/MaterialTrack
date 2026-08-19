<?php

namespace App\Http\Controllers\Apps;

use App\Exports\StockOpnamesExport;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockOpname;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $opnames = StockOpname::with(['item.category', 'user', 'verifiedBy'])
            ->where('period', $period)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $availablePeriods = StockOpname::select('period')
            ->distinct()
            ->orderByDesc('period')
            ->pluck('period');

        return inertia('Apps/StockOpnames/Index', [
            'opnames' => $opnames,
            'availablePeriods' => $availablePeriods,
            'currentPeriod' => $period,
        ]);
    }

    public function create(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $items = Item::with('category:id,name')->orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock', 'category_id']);

        $alreadyDone = StockOpname::where('period', $period)->pluck('item_id')->toArray();

        return inertia('Apps/StockOpnames/Create', [
            'items' => $items,
            'period' => $period,
            'alreadyDone' => $alreadyDone,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|date_format:Y-m',
            'date' => 'required|date',
            'entries' => 'required|array|min:1',
            'entries.*.item_id' => 'required|exists:items,id',
            'entries.*.physical_stock' => 'required|integer|min:0',
            'entries.*.notes' => 'nullable|string',
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

                $refNo = 'OPN-'.str_replace('-', '', $validated['period']).'-'.str_pad(
                    StockOpname::where('period', $validated['period'])->count() + 1, 4, '0', STR_PAD_LEFT
                );

                // Status pending: stok BELUM berubah sampai diverifikasi oleh
                // petugas kedua (verifikasi ganda).
                StockOpname::create([
                    'reference_no' => $refNo,
                    'period' => $validated['period'],
                    'date' => $validated['date'],
                    'item_id' => $item->id,
                    'user_id' => $user->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $difference,
                    'notes' => $entry['notes'] ?? null,
                    'status' => 'pending',
                ]);

                $count++;
            }
        });

        ActivityLogService::log('stock_opname.create', null, ['item_count' => $count, 'period' => $validated['period']]);

        return to_route('apps.stock-opnames.index', ['period' => $validated['period']])
            ->with('success', "{$count} item berhasil dicatat dalam stock opname periode {$validated['period']}. Menunggu verifikasi.");
    }

    public function verify(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'pending') {
            return back()->withErrors(['error' => 'Opname ini sudah diverifikasi.']);
        }

        $user = auth()->user();

        // Verifikasi ganda: verifikator harus orang berbeda dari petugas pencatat
        if ($stockOpname->user_id === $user->id) {
            return back()->withErrors(['error' => 'Verifikasi harus dilakukan oleh petugas yang berbeda dari pencatat opname.']);
        }

        DB::transaction(function () use ($stockOpname, $user) {
            $item = Item::lockForUpdate()->findOrFail($stockOpname->item_id);

            // Terapkan hasil opname ke stok sistem
            $item->current_stock = $stockOpname->physical_stock;
            $item->save();

            // Movement 'adjustment' dicatat otomatis oleh StockOpnameObserver
            $stockOpname->update([
                'status' => 'verified',
                'verified_by' => $user->id,
                'verified_at' => now(),
            ]);
        });

        ActivityLogService::log('stock_opname.verify', $stockOpname, [
            'item_name' => $stockOpname->item->name ?? '',
            'physical_stock' => $stockOpname->physical_stock,
        ]);

        return back()->with('success', "Opname {$stockOpname->reference_no} terverifikasi. Stok sistem disesuaikan ke {$stockOpname->physical_stock}.");
    }

    public function show(StockOpname $stockOpname)
    {
        return inertia('Apps/StockOpnames/Show', [
            'opname' => $stockOpname->load(['item.category', 'user', 'verifiedBy']),
        ]);
    }

    public function edit(StockOpname $stockOpname)
    {
        return inertia('Apps/StockOpnames/Edit', [
            'opname' => $stockOpname->load(['item.category', 'user']),
        ]);
    }

    public function update(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'pending') {
            return back()->withErrors(['error' => 'Opname yang sudah diverifikasi tidak dapat direvisi.']);
        }

        $validated = $request->validate([
            'physical_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $newDifference = $validated['physical_stock'] - $stockOpname->system_stock;

        $stockOpname->update([
            'physical_stock' => $validated['physical_stock'],
            'difference' => $newDifference,
            'notes' => $validated['notes'] ?? $stockOpname->notes,
        ]);

        ActivityLogService::log('stock_opname.update', $stockOpname, [
            'item_name' => $stockOpname->item->name ?? '',
            'new_count' => $validated['physical_stock'],
        ]);

        return to_route('apps.stock-opnames.index', ['period' => $stockOpname->period])
            ->with('success', 'Stock opname berhasil diperbarui.');
    }

    public function destroy(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'pending') {
            return back()->withErrors(['error' => 'Opname yang sudah diverifikasi tidak dapat dihapus; gunakan revisi/penyesuaian baru.']);
        }

        $stockOpname->delete();

        return to_route('apps.stock-opnames.index', ['period' => $stockOpname->period])
            ->with('success', 'Stock opname berhasil dihapus.');
    }

    public function downloadPdf(StockOpname $stockOpname)
    {
        $pdf = Pdf::loadView('pdf.stock-opname', [
            'opname' => $stockOpname->load(['item.category', 'user']),
        ]);

        return $pdf->download('StockOpname-'.$stockOpname->reference_no.'.pdf');
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
            'period' => $period,
        ]);

        return $pdf->download('StockOpname-Rekap-'.str_replace('-', '', $period).'.pdf');
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
            'MaterialTrack-StockOpname-'.str_replace('-', '', $period).'-'.now()->format('His').'.xlsx'
        );
    }
}
