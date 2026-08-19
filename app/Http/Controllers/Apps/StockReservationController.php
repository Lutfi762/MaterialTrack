<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Department;
use App\Models\StockReservation;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = StockReservation::with('item', 'department', 'user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->input('item_id'));
        }

        $reservations = $query->latest()->paginate(20);
        $items = Item::orderBy('name')->get(['id', 'name', 'code', 'current_stock']);

        return Inertia::render('Apps/StockReservations/Index', [
            'reservations' => $reservations,
            'items' => $items,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $items = Item::orderBy('name')->get(['id', 'name', 'code', 'current_stock', 'min_stock']);
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Apps/StockReservations/Create', [
            'items' => $items,
            'departments' => $departments,
            'user_department_id' => $user->department_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'department_id' => 'nullable|exists:departments,id',
            'qty' => 'required|integer|min:1',
            'purpose' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'reserved_until' => 'nullable|date|after:today',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $reservedStock = StockReservation::where('item_id', $item->id)
            ->where('status', 'active')
            ->sum('qty');

        $available = $item->current_stock - $reservedStock;

        if ($validated['qty'] > $available) {
            return back()->withErrors([
                'qty' => "Stok tersedia tidak cukup. Tersedia: {$available} (stok aktual: {$item->current_stock}, sudah direserv: {$reservedStock})",
            ])->withInput();
        }

        $validated['user_id'] = auth()->id();

        $reservation = StockReservation::create($validated);

        ActivityLogService::log('stock_reservation.create', $reservation, [
            'item_name' => $item->name,
            'qty' => $validated['qty'],
            'project' => $validated['project_name'] ?? '',
        ]);

        return to_route('apps.stock-reservations.index')
            ->with('success', "Stok berhasil direserv untuk {$item->name}.");
    }

    public function show(StockReservation $stockReservation)
    {
        $stockReservation->load('item', 'department', 'user');

        return Inertia::render('Apps/StockReservations/Show', [
            'reservation' => $stockReservation,
        ]);
    }

    public function fulfill(StockReservation $stockReservation)
    {
        if ($stockReservation->status !== 'active') {
            return back()->withErrors(['error' => 'Reservasi ini sudah tidak aktif.']);
        }

        $stockReservation->update(['status' => 'fulfilled']);

        ActivityLogService::log('stock_reservation.fulfill', $stockReservation, [
            'item_name' => $stockReservation->item->name ?? '',
            'qty' => $stockReservation->qty,
        ]);

        return back()->with('success', 'Reservasi ditandai sebagai sudah diambil.');
    }

    public function cancel(StockReservation $stockReservation)
    {
        if ($stockReservation->status !== 'active') {
            return back()->withErrors(['error' => 'Reservasi ini sudah tidak aktif.']);
        }

        $stockReservation->update(['status' => 'cancelled']);

        ActivityLogService::log('stock_reservation.cancel', $stockReservation, [
            'item_name' => $stockReservation->item->name ?? '',
            'qty' => $stockReservation->qty,
        ]);

        return back()->with('success', 'Reservasi dibatalkan.');
    }

    public function destroy(StockReservation $stockReservation)
    {
        $stockReservation->delete();

        return to_route('apps.stock-reservations.index')
            ->with('success', 'Reservasi dihapus.');
    }
}
