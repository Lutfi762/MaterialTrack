<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Exports\StockMovementsExport;
use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['item.category', 'user', 'pic'])->latest();

        if ($request->filled('department_id')) {
            $query->whereHas('item.category.departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return Inertia::render('Apps/StockMovements/Index', [
            'movements'   => $query->paginate(20)->withQueryString(),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'items'       => Item::with('category.departments:id,name')->orderBy('name')->get(['id', 'name', 'code', 'category_id'])
                ->map(fn ($item) => [
                    'id'             => $item->id,
                    'name'           => $item->name,
                    'code'           => $item->code,
                    'category_id'    => $item->category_id,
                    'department_ids' => $item->category?->departments->pluck('id') ?? [],
                ]),
            'categories'  => Category::with('departments:id')->orderBy('name')->get(['id', 'name']),
            'filters'     => $request->only(['item_id', 'type', 'date_from', 'date_to', 'category_id', 'department_id']),
        ]);
    }

    public function downloadAllPdf(Request $request)
    {
        $query = StockMovement::with(['item.category', 'user', 'pic'])->latest();

        if ($request->filled('department_id')) {
            $query->whereHas('item.category.departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->get();

        $pdf = Pdf::loadView('pdf.stock-movements-batch', [
            'movements' => $movements,
            'filters'   => $request->only(['item_id', 'type', 'date_from', 'date_to', 'category_id', 'department_id']),
        ]);

        return $pdf->download('MaterialTrack-RiwayatPergerakan-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = StockMovement::query()->latest();

        if ($request->filled('department_id')) {
            $query->whereHas('item.category.departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return Excel::download(
            new StockMovementsExport($query),
            'MaterialTrack-RiwayatPergerakan-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}