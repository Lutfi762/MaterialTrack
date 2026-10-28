<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use App\Models\Department;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = FixedAsset::with(["department", "assetAccount", "depreciationAccount", "accumulatedDepAccount"]);
        if ($request->search) {
            $query->where("name", "like", "%" . $request->search . "%")
                  ->orWhere("code", "like", "%" . $request->search . "%");
        }
        if ($request->status) {
            $query->where("status", $request->status);
        }
        if ($request->category) {
            $query->where("category", $request->category);
        }
        
        return Inertia::render("Apps/FixedAssets/Index", [
            "fixedAssets" => $query->paginate(15)->withQueryString(),
            "filters" => $request->only(["search", "status", "category"])
        ]);
    }

    public function create()
    {
        $accounts = ChartOfAccount::where("is_active", true)
            ->where("is_header", false)
            ->whereIn("account_type", ["asset", "expense"])
            ->get();

        return Inertia::render("Apps/FixedAssets/Create", [
            "departments" => Department::where("is_active", true)->get(),
            "accounts" => $accounts
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "code" => "required|string|unique:fixed_assets",
            "name" => "required|string",
            "description" => "nullable|string",
            "category" => "required|in:building,vehicle,machinery,equipment,furniture,other",
            "acquisition_date" => "required|date",
            "acquisition_cost" => "required|numeric|min:0",
            "useful_life_months" => "required|integer|min:1",
            "salvage_value" => "nullable|numeric|min:0",
            "depreciation_method" => "nullable|in:straight_line,declining_balance",
            "department_id" => "nullable|exists:departments,id",
            "asset_account_id" => "nullable|exists:chart_of_accounts,id",
            "depreciation_account_id" => "nullable|exists:chart_of_accounts,id",
            "accumulated_dep_account_id" => "nullable|exists:chart_of_accounts,id",
            "notes" => "nullable|string",
        ]);

        $validated["salvage_value"] = $validated["salvage_value"] ?? 0;
        $validated["current_book_value"] = $validated["acquisition_cost"];
        $validated["accumulated_depreciation"] = 0;
        $validated["status"] = "active";

        FixedAsset::create($validated);
        return to_route("apps.fixed-assets.index")->with("success", "Fixed asset created.");
    }

    public function edit(FixedAsset $fixedAsset)
    {
        $accounts = ChartOfAccount::where("is_active", true)
            ->where("is_header", false)
            ->whereIn("account_type", ["asset", "expense"])
            ->get();

        return Inertia::render("Apps/FixedAssets/Edit", [
            "fixedAsset" => $fixedAsset,
            "departments" => Department::where("is_active", true)->get(),
            "accounts" => $accounts
        ]);
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $validated = $request->validate([
            "code" => "required|string|unique:fixed_assets,code," . $fixedAsset->id,
            "name" => "required|string",
            "description" => "nullable|string",
            "category" => "required|in:building,vehicle,machinery,equipment,furniture,other",
            "acquisition_date" => "required|date",
            "acquisition_cost" => "required|numeric|min:0",
            "useful_life_months" => "required|integer|min:1",
            "salvage_value" => "nullable|numeric|min:0",
            "depreciation_method" => "nullable|in:straight_line,declining_balance",
            "department_id" => "nullable|exists:departments,id",
            "asset_account_id" => "nullable|exists:chart_of_accounts,id",
            "depreciation_account_id" => "nullable|exists:chart_of_accounts,id",
            "accumulated_dep_account_id" => "nullable|exists:chart_of_accounts,id",
            "notes" => "nullable|string",
            "status" => "required|in:active,disposed,fully_depreciated"
        ]);

        $fixedAsset->update($validated);
        return to_route("apps.fixed-assets.index")->with("success", "Fixed asset updated.");
    }

    public function destroy(FixedAsset $fixedAsset)
    {
        $fixedAsset->delete();
        return to_route("apps.fixed-assets.index")->with("success", "Fixed asset deleted.");
    }

    public function runDepreciation(FixedAsset $fixedAsset)
    {
        if ($fixedAsset->status !== "active" || $fixedAsset->current_book_value <= $fixedAsset->salvage_value) {
            return back()->with("error", "Asset cannot be depreciated.");
        }

        DB::transaction(function () use ($fixedAsset) {
            $depAmount = $fixedAsset->monthlyDepreciation();
            if ($fixedAsset->current_book_value - $depAmount < $fixedAsset->salvage_value) {
                $depAmount = $fixedAsset->current_book_value - $fixedAsset->salvage_value;
            }

            if ($depAmount <= 0) return;

            $je = JournalEntry::create([
                "entry_number" => DocumentNumber::next("journal_entries", "JE"),
                "entry_date" => now(),
                "reference_type" => "FixedAsset",
                "reference_id" => $fixedAsset->id,
                "description" => "Depreciation for " . $fixedAsset->code,
                "total_debit" => $depAmount,
                "total_credit" => $depAmount,
                "status" => "posted",
                "created_by" => auth()->id() ?? 1
            ]);

            if ($fixedAsset->depreciation_account_id) {
                $je->lines()->create([
                    "chart_of_account_id" => $fixedAsset->depreciation_account_id,
                    "debit" => $depAmount,
                    "credit" => 0,
                    "description" => "Depreciation expense"
                ]);
            }

            if ($fixedAsset->accumulated_dep_account_id) {
                $je->lines()->create([
                    "chart_of_account_id" => $fixedAsset->accumulated_dep_account_id,
                    "debit" => 0,
                    "credit" => $depAmount,
                    "description" => "Accumulated depreciation"
                ]);
            }

            $fixedAsset->accumulated_depreciation += $depAmount;
            $fixedAsset->current_book_value -= $depAmount;
            if ($fixedAsset->current_book_value <= $fixedAsset->salvage_value) {
                $fixedAsset->status = "fully_depreciated";
            }
            $fixedAsset->save();
        });

        return back()->with("success", "Depreciation recorded successfully.");
    }
}
