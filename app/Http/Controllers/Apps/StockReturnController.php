<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Item;
use App\Models\StockReturn;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockReturnController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = StockReturn::with(['item', 'department', 'user', 'approver'])->latest();

        if ($user->hasRole('department_user')) {
            $query->where('department_id', $user->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $departmentsQuery = Department::orderBy('name');
        if ($user->hasRole('department_user')) {
            $departmentsQuery->where('id', $user->department_id);
        }
        $departments = $departmentsQuery->get(['id', 'name']);

        $pendingCount = StockReturn::where('status', 'pending')
            ->when($user->hasRole('department_user'), fn ($q) => $q->where('department_id', $user->department_id))
            ->count();

        return inertia('Apps/StockReturns/Index', [
            'stockReturns' => $query->paginate(15)->withQueryString(),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock']),
            'departments' => $departments,
            'filters' => $request->only(['status', 'item_id', 'department_id']),
            'pendingCount' => $pendingCount,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $itemsQuery = Item::with('category:id,name');

        if ($user->hasRole('department_user')) {
            $itemsQuery->whereHas('category.departments', function ($q) use ($user) {
                $q->where('departments.id', $user->department_id);
            });
        }

        $items = $itemsQuery->orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock']);

        if ($user->hasRole('department_user')) {
            $departments = Department::where('id', $user->department_id)->get(['id', 'name']);
        } else {
            $departments = Department::orderBy('name')->get(['id', 'name']);
        }

        return inertia('Apps/StockReturns/Create', [
            'items' => $items,
            'departments' => $departments,
            'lockedDepartmentId' => $user->hasRole('department_user') ? $user->department_id : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'department_id' => 'nullable|exists:departments,id',
            'qty' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        if (isset($validated['reason'])) {
            $validated['reason'] = strip_tags($validated['reason']);
        }

        if ($user->hasRole('department_user') && ! empty($validated['department_id']) && $validated['department_id'] != $user->department_id) {
            abort(403, 'Anda hanya bisa membuat retur untuk departemen Anda sendiri.');
        }

        $item = Item::findOrFail($validated['item_id']);

        if ($validated['qty'] > $item->current_stock) {
            return back()->withErrors(['qty' => 'Jumlah retur melebihi stok tersedia. Stok saat ini: '.$item->current_stock.' '.$item->unit.'.']);
        }

        $stockReturn = StockReturn::create([
            'item_id' => $validated['item_id'],
            'department_id' => $validated['department_id'] ?? null,
            'user_id' => $user->id,
            'qty' => $validated['qty'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        ActivityLogService::log('stock_return.create', $stockReturn, [
            'item_name' => $stockReturn->item->name ?? '',
            'qty' => $validated['qty'],
        ]);

        return to_route('apps.stock-returns.index')->with('success', 'Permintaan retur material berhasil diajukan. Menunggu persetujuan admin.');
    }

    public function approve(StockReturn $stockReturn)
    {
        if ($stockReturn->status !== 'pending') {
            return back()->withErrors(['error' => 'Transaksi ini tidak dalam status pending.']);
        }

        $user = auth()->user();
        if (! $user->hasAnyPermission(['stock_outs.approve', 'roles.admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui retur.');
        }

        DB::transaction(function () use ($stockReturn, $user) {
            $item = Item::lockForUpdate()->findOrFail($stockReturn->item_id);

            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore + $stockReturn->qty;

            $item->current_stock = $stockAfter;
            $item->save();

            // Movement 'in' dicatat otomatis oleh StockReturnObserver
            // saat status berubah menjadi approved

            $stockReturn->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });

        ActivityLogService::log('stock_return.approve', $stockReturn, [
            'item_name' => $stockReturn->item->name ?? '',
            'qty' => $stockReturn->qty,
        ]);

        return back()->with('success', 'Retur material berhasil disetujui. Stok telah dikembalikan.');
    }

    public function reject(StockReturn $stockReturn)
    {
        if ($stockReturn->status !== 'pending') {
            return back()->withErrors(['error' => 'Transaksi ini tidak dalam status pending.']);
        }

        $user = auth()->user();
        if (! $user->hasAnyPermission(['stock_outs.approve', 'roles.admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk menolak retur.');
        }

        $stockReturn->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        ActivityLogService::log('stock_return.reject', $stockReturn, [
            'item_name' => $stockReturn->item->name ?? '',
            'qty' => $stockReturn->qty,
        ]);

        return back()->with('success', 'Retur material ditolak.');
    }
}
