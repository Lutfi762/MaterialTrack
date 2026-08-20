<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::with('parent');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%");
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return Inertia::render('Apps/ChartOfAccounts/Index', [
            'accounts' => $query->orderBy('code')->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/ChartOfAccounts/Create', [
            'headerAccounts' => ChartOfAccount::where('is_header', true)->orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'sub_type' => 'required|string',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_header' => 'boolean',
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        ChartOfAccount::create($validated);

        return to_route('apps.chart-of-accounts.index')->with('success', 'Akun Akuntansi berhasil dibuat.');
    }

    public function edit(ChartOfAccount $chartOfAccount)
    {
        return Inertia::render('Apps/ChartOfAccounts/Edit', [
            'account' => $chartOfAccount,
            'headerAccounts' => ChartOfAccount::where('is_header', true)
                ->where('id', '!=', $chartOfAccount->id)
                ->orderBy('code')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:chart_of_accounts,code,' . $chartOfAccount->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'sub_type' => 'required|string',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_header' => 'boolean',
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $chartOfAccount->update($validated);

        return to_route('apps.chart-of-accounts.index')->with('success', 'Akun Akuntansi berhasil diperbarui.');
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();
        return to_route('apps.chart-of-accounts.index')->with('success', 'Akun Akuntansi berhasil dihapus.');
    }
}
