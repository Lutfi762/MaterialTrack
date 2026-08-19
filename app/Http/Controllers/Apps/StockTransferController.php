<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Item;
use App\Models\StockTransfer;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::with(['item', 'fromDepartment', 'toDepartment', 'user', 'approver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->where(function ($q) use ($deptId) {
                $q->where('from_department_id', $deptId)
                    ->orWhere('to_department_id', $deptId);
            });
        }

        $transfers = $query->paginate(15);

        return Inertia::render('Apps/StockTransfers/Index', [
            'transfers' => $transfers,
            'departments' => Department::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $items = Item::with('category:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'unit', 'current_stock', 'category_id']);

        $departments = Department::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Apps/StockTransfers/Create', [
            'items' => $items,
            'departments' => $departments,
            'userDepartment' => $user->department_id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'from_department_id' => 'required|exists:departments,id',
            'to_department_id' => 'required|exists:departments,id|different:from_department_id',
            'qty' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check stock availability in source department
        $item = Item::find($request->item_id);
        if ($item->current_stock < $request->qty) {
            return back()->withErrors(['qty' => 'Stok tidak mencukupi. Stok tersisa: '.$item->current_stock.' '.$item->unit]);
        }

        $transfer = StockTransfer::create([
            'reference_no' => StockTransfer::generateReferenceNo(),
            'item_id' => $request->item_id,
            'from_department_id' => $request->from_department_id,
            'to_department_id' => $request->to_department_id,
            'qty' => $request->qty,
            'unit' => $item->unit,
            'status' => 'pending',
            'user_id' => auth()->id(),
            'notes' => $request->notes,
        ]);

        ActivityLogService::log('stock_transfer.create', $transfer, ['reference_no' => $transfer->reference_no, 'item_name' => $item->name, 'qty' => $request->qty]);

        return to_route('apps.stock-transfers.index')
            ->with('success', 'Transfer stok berhasil dibuat. Menunggu persetujuan admin.');
    }

    public function show(StockTransfer $stockTransfer)
    {
        return inertia('Apps/StockTransfers/Show', [
            'transfer' => $stockTransfer->load(['item', 'fromDepartment', 'toDepartment', 'user', 'approver']),
        ]);
    }

    public function approve(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'Transfer sudah diproses.']);
        }

        $item = Item::find($stockTransfer->item_id);
        if ($item->current_stock < $stockTransfer->qty) {
            return back()->withErrors(['error' => 'Stok tidak mencukupi untuk menyelesaikan transfer.']);
        }

        DB::transaction(function () use ($stockTransfer, $item) {
            // Decrease stock from source
            $item->decrement('current_stock', $stockTransfer->qty);

            // Movement 'out' + 'adjustment' dicatat otomatis oleh
            // StockTransferObserver saat status berubah menjadi approved

            $stockTransfer->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        ActivityLogService::log('stock_transfer.approve', $stockTransfer, ['reference_no' => $stockTransfer->reference_no]);

        return back()->with('success', 'Transfer stok berhasil disetujui.');
    }

    public function reject(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'Transfer sudah diproses.']);
        }

        $stockTransfer->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Transfer stok ditolak.');
    }

    public function downloadPdf(StockTransfer $stockTransfer)
    {
        $pdf = Pdf::loadView('pdf.stock-transfer', [
            'transfer' => $stockTransfer->load(['item', 'fromDepartment', 'toDepartment', 'user', 'approver']),
        ]);

        return $pdf->download('TransferStok-'.$stockTransfer->reference_no.'.pdf');
    }

    public function downloadAllPdf(Request $request)
    {
        $query = StockTransfer::with(['item', 'fromDepartment', 'toDepartment', 'user', 'approver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query->get();

        $pdf = Pdf::loadView('pdf.stock-transfers-batch', [
            'transfers' => $transfers,
        ]);

        return $pdf->download('MaterialTrack-TransferStok-Rekap-'.now()->format('Ymd-His').'.pdf');
    }
}
