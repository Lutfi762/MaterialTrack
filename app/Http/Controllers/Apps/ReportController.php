<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockIn;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOut;
use App\Models\StockReturn;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Stock In summary
        $stockInCount = StockIn::whereBetween('date', [$dateFrom, $dateTo])->count();
        $stockInQty = StockIn::whereBetween('date', [$dateFrom, $dateTo])->sum('qty');

        // Stock Out summary
        $stockOutCount = StockOut::whereBetween('date', [$dateFrom, $dateTo])->count();
        $stockOutQty = StockOut::whereBetween('date', [$dateFrom, $dateTo])->sum('qty');
        $pendingApproval = StockOut::where('status', 'pending')->count();

        // Stock Opname summary
        $opnameCount = StockOpname::whereBetween('date', [$dateFrom, $dateTo])->count();
        $opnameSurplus = StockOpname::whereBetween('date', [$dateFrom, $dateTo])->where('difference', '>', 0)->count();
        $opnameDefisit = StockOpname::whereBetween('date', [$dateFrom, $dateTo])->where('difference', '<', 0)->count();

        // Stock Movements
        $movementCount = StockMovement::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();

        // Item stats
        $totalItems = Item::count();
        $lowStockItems = Item::lowStock()->count();
        $outOfStock = Item::outOfStock()->count();

        return Inertia::render('Apps/Reports/Index', [
            'summary' => [
                'stock_in_count' => $stockInCount,
                'stock_in_qty' => $stockInQty,
                'stock_out_count' => $stockOutCount,
                'stock_out_qty' => $stockOutQty,
                'pending_approval' => $pendingApproval,
                'opname_count' => $opnameCount,
                'opname_surplus' => $opnameSurplus,
                'opname_defisit' => $opnameDefisit,
                'movement_count' => $movementCount,
                'total_items' => $totalItems,
                'low_stock_items' => $lowStockItems,
                'out_of_stock' => $outOfStock,
            ],
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Kartu stok per item: saldo awal + mutasi berjalan pada satu periode.
     * Mencakup semua sumber yang mengubah current_stock: penerimaan, pengeluaran
     * disetujui, penyesuaian manual disetujui, retur disetujui, opname terverifikasi,
     * dan transfer keluar disetujui (transfer mengurangi current_stock global saat approve).
     */
    private function buildStockCardData(Request $request): array
    {
        $from = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('date_to', now()->format('Y-m-d'));
        $itemId = (int) $request->input('item_id', 0);

        $result = [
            'item' => null,
            'date_from' => $from,
            'date_to' => $to,
            'item_id' => $itemId,
            'opening' => 0,
            'rows' => [],
        ];

        if (! $itemId || ! $item = Item::find($itemId)) {
            return $result;
        }

        $result['item'] = ['id' => $item->id, 'name' => $item->name, 'code' => $item->code, 'unit' => $item->unit];

        // Saldo sebelum periode: penerimaan - pengeluaran disetujui + penyesuaian
        // + retur disetujui + selisih opname terverifikasi - transfer keluar disetujui
        $inBefore = StockIn::where('item_id', $itemId)->where('date', '<', $from)->sum('qty');
        $outBefore = StockOut::where('item_id', $itemId)->where('status', 'approved')->where('date', '<', $from)->sum('qty');
        $adjBefore = StockAdjustment::where('item_id', $itemId)->where('status', 'approved')->whereDate('approved_at', '<', $from)->sum('qty');
        $retBefore = StockReturn::where('item_id', $itemId)->where('status', 'approved')->whereDate('approved_at', '<', $from)->sum('qty');
        $opnameBefore = StockOpname::where('item_id', $itemId)->where('status', 'verified')->whereDate('verified_at', '<', $from)->sum('difference');
        $transferBefore = StockTransfer::where('item_id', $itemId)->where('status', 'approved')->whereDate('approved_at', '<', $from)->sum('qty');
        $result['opening'] = $inBefore - $outBefore + $adjBefore + $retBefore + $opnameBefore - $transferBefore;

        $ins = StockIn::where('item_id', $itemId)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->map(fn ($r) => ['tgl' => (string) $r->date, 'qty' => (int) $r->qty, 'ref' => $r->reference_no, 'ket' => 'Penerimaan' . ($r->source ? " ({$r->source})" : '')]);

        $outs = StockOut::where('item_id', $itemId)
            ->where('status', 'approved')
            ->whereBetween('date', [$from, $to])
            ->get()
            ->map(fn ($r) => ['tgl' => (string) $r->date, 'qty' => -(int) $r->qty, 'ref' => $r->reference_no, 'ket' => 'Pengeluaran']);

        $adjs = StockAdjustment::where('item_id', $itemId)
            ->where('status', 'approved')
            ->whereBetween(DB::raw('DATE(approved_at)'), [$from, $to])
            ->get()
            ->map(fn ($r) => ['tgl' => substr((string) $r->approved_at, 0, 10), 'qty' => (int) $r->qty, 'ref' => $r->reference_no, 'ket' => 'Penyesuaian stok']);

        $rets = StockReturn::where('item_id', $itemId)
            ->where('status', 'approved')
            ->whereBetween(DB::raw('DATE(approved_at)'), [$from, $to])
            ->get()
            ->map(fn ($r) => ['tgl' => substr((string) $r->approved_at, 0, 10), 'qty' => (int) $r->qty, 'ref' => null, 'ket' => 'Retur masuk']);

        $opnames = StockOpname::where('item_id', $itemId)
            ->where('status', 'verified')
            ->whereBetween(DB::raw('DATE(verified_at)'), [$from, $to])
            ->get()
            ->map(fn ($r) => ['tgl' => substr((string) $r->verified_at, 0, 10), 'qty' => (int) $r->difference, 'ref' => $r->reference_no, 'ket' => 'Stock Opname ' . $r->period . ($r->difference >= 0 ? ' (surplus)' : ' (defisit)')]);

        $transfers = StockTransfer::where('item_id', $itemId)
            ->where('status', 'approved')
            ->whereBetween(DB::raw('DATE(approved_at)'), [$from, $to])
            ->with('toDepartment')
            ->get()
            ->map(fn ($r) => ['tgl' => substr((string) $r->approved_at, 0, 10), 'qty' => -(int) $r->qty, 'ref' => $r->reference_no, 'ket' => 'Transfer ke ' . ($r->toDepartment->name ?? '-')]);

        $result['rows'] = collect([$ins, $outs, $adjs, $rets, $opnames, $transfers])
            ->flatten(1)
            ->sortBy('tgl')
            ->values()
            ->all();

        return $result;
    }

    public function stockCard(Request $request)
    {
        $data = $this->buildStockCardData($request);

        return Inertia::render('Apps/Reports/StockCard', [
            'card' => $data,
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit']),
            'filters' => [
                'item_id' => $data['item_id'],
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
            ],
        ]);
    }

    public function exportStockCard(Request $request)
    {
        $data = $this->buildStockCardData($request);
        $filename = 'kartu-stok-' . ($data['item']['code'] ?? 'pilih-item') . "-{$data['date_from']}_{$data['date_to']}.csv";

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Referensi', 'Keterangan', 'Masuk', 'Keluar', 'Saldo']);
            $saldo = $data['opening'];
            fputcsv($out, ['', '', 'Saldo Awal', '', '', $saldo]);
            foreach ($data['rows'] as $row) {
                $saldo += $row['qty'];
                fputcsv($out, [$row['tgl'], $row['ref'], $row['ket'], $row['qty'] > 0 ? $row['qty'] : '', $row['qty'] < 0 ? -$row['qty'] : '', $saldo]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
