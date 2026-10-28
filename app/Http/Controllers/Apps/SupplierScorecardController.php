<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\SupplierScorecard;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierScorecardController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierScorecard::with("supplier");
        if ($request->supplier_id) $query->where("supplier_id", $request->supplier_id);
        if ($request->period) $query->where("period", $request->period);

        return Inertia::render("Apps/SupplierScorecards/Index", [
            "scorecards" => $query->paginate(15)->withQueryString(),
            "filters" => $request->only(["supplier_id", "period"]),
            "suppliers" => Supplier::where("is_active", true)->get(),
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate(["period" => "required|date_format:Y-m"]);
        $period = $request->period;
        
        $start = $period . "-01";
        $end = date("Y-m-t", strtotime($start));

        $suppliers = Supplier::where("is_active", true)->get();
        foreach ($suppliers as $supplier) {
            $pos = PurchaseOrder::where("supplier_id", $supplier->id)
                ->whereBetween("po_date", [$start, $end])
                ->get();
                
            $total_po = $pos->count();
            $total_po_value = $pos->sum("grand_total");

            $grs = GoodsReceipt::whereHas("purchaseOrder", function($q) use ($supplier) {
                $q->where("supplier_id", $supplier->id);
            })->whereBetween("receipt_date", [$start, $end])->with("items")->get();

            $on_time = 0;
            $late = 0;
            $total_recv = 0;
            $total_rej = 0;

            foreach ($grs as $gr) {
                $expected = $gr->purchaseOrder->expected_delivery_date;
                if ($expected && $gr->receipt_date <= $expected) {
                    $on_time++;
                } else {
                    $late++;
                }
                
                foreach ($gr->items as $item) {
                    $total_recv += $item->qty_received;
                    $total_rej += $item->qty_rejected;
                }
            }

            $del_score = ($on_time + $late) > 0 ? ($on_time / ($on_time + $late)) * 100 : 0;
            $qual_score = $total_recv > 0 ? (($total_recv - $total_rej) / $total_recv) * 100 : 0;
            $overall = ($del_score + $qual_score) / 2;

            SupplierScorecard::updateOrCreate(
                ["supplier_id" => $supplier->id, "period" => $period],
                [
                    "total_po" => $total_po,
                    "total_po_value" => $total_po_value,
                    "on_time_deliveries" => $on_time,
                    "late_deliveries" => $late,
                    "total_qty_received" => $total_recv,
                    "total_qty_rejected" => $total_rej,
                    "delivery_score" => $del_score,
                    "quality_score" => $qual_score,
                    "overall_score" => $overall
                ]
            );
        }

        return back()->with("success", "Scorecards generated for " . $period);
    }
}
