<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Department;
use App\Models\FiscalYear;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = Budget::with(["fiscalYear", "department", "account"]);
        if ($request->fiscal_year_id) $query->where("fiscal_year_id", $request->fiscal_year_id);
        if ($request->department_id) $query->where("department_id", $request->department_id);

        return Inertia::render("Apps/Budgets/Index", [
            "budgets" => $query->paginate(15)->withQueryString(),
            "filters" => $request->only(["fiscal_year_id", "department_id"]),
            "fiscalYears" => FiscalYear::all(),
            "departments" => Department::where("is_active", true)->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render("Apps/Budgets/Create", [
            "fiscalYears" => FiscalYear::all(),
            "departments" => Department::where("is_active", true)->get(),
            "accounts" => ChartOfAccount::where("is_active", true)->where("is_header", false)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "fiscal_year_id" => "required|exists:fiscal_years,id",
            "department_id" => "nullable|exists:departments,id",
            "chart_of_account_id" => "required|exists:chart_of_accounts,id",
            "budget_amount" => "required|numeric|min:0",
            "notes" => "nullable|string",
        ]);

        $exists = Budget::where("fiscal_year_id", $validated["fiscal_year_id"])
            ->where("department_id", $validated["department_id"])
            ->where("chart_of_account_id", $validated["chart_of_account_id"])
            ->exists();

        if ($exists) {
            return back()->withErrors(["chart_of_account_id" => "Budget for this account and department already exists in this fiscal year."]);
        }

        Budget::create($validated);
        return to_route("apps.budgets.index")->with("success", "Budget created.");
    }

    public function edit(Budget $budget)
    {
        return Inertia::render("Apps/Budgets/Edit", [
            "budget" => $budget,
            "fiscalYears" => FiscalYear::all(),
            "departments" => Department::where("is_active", true)->get(),
            "accounts" => ChartOfAccount::where("is_active", true)->where("is_header", false)->get()
        ]);
    }

    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            "budget_amount" => "required|numeric|min:0",
            "notes" => "nullable|string",
        ]);

        $budget->update($validated);
        return to_route("apps.budgets.index")->with("success", "Budget updated.");
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return to_route("apps.budgets.index")->with("success", "Budget deleted.");
    }

    public function updateRealization(Budget $budget)
    {
        $fy = $budget->fiscalYear;
        $realized = JournalEntryLine::where("chart_of_account_id", $budget->chart_of_account_id)
            ->whereHas("journalEntry", function($q) use ($fy) {
                $q->where("status", "posted")
                  ->whereBetween("entry_date", [$fy->start_date, $fy->end_date]);
            })
            ->sum("debit");

        $budget->update(["realized_amount" => $realized]);

        if ($budget->realized_amount > $budget->budget_amount) {
            AppNotification::create([
                "user_id" => auth()->id() ?? 1,
                "title" => "Budget Exceeded",
                "message" => "Budget for account " . $budget->account->name . " exceeded.",
                "url" => "/apps/budgets",
                "is_read" => false
            ]);
        }

        return back()->with("success", "Realization updated.");
    }
}
