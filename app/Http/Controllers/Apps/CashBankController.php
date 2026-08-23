<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\CashBankTransaction;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashBankController extends Controller
{
    public function index(Request $request)
    {
        $query = CashBankTransaction::with(['account', 'counterAccount', 'creator'])
            ->latest('transaction_date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        return Inertia::render('Apps/CashBank/Index', [
            'transactions' => $query->paginate(15)->withQueryString(),
            'accounts' => $this->cashAccounts(),
            'filters' => $request->only(['type', 'account_id', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/CashBank/Create', [
            'accounts' => $this->cashAccounts(),
            'counterAccounts' => ChartOfAccount::where('is_header', false)
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'type' => 'required|in:in,out',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'counter_account_id' => 'required|exists:chart_of_accounts,id|different:account_id',
            'amount' => 'required|numeric|min:1',
            'reference_no' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        DB::transaction(function () use ($validated, $user) {
            $transactionNumber = DocumentNumber::next('cash_bank_transactions', 'CB');
            $isIn = $validated['type'] === 'in';
            $description = strip_tags($validated['description'] ?? '');

            CashBankTransaction::create([
                'transaction_number' => $transactionNumber,
                'transaction_date' => $validated['transaction_date'],
                'type' => $validated['type'],
                'account_id' => $validated['account_id'],
                'counter_account_id' => $validated['counter_account_id'],
                'amount' => $validated['amount'],
                'reference_no' => $validated['reference_no'] ?? null,
                'description' => $description,
                'created_by' => $user->id,
            ]);

            $entry = JournalEntry::create([
                'entry_number' => DocumentNumber::next('journal_entries', 'JV'),
                'entry_date' => $validated['transaction_date'],
                'reference_no' => $transactionNumber,
                'source' => 'cash_bank',
                'narration' => ucfirst($isIn ? 'Kas masuk' : 'Kas keluar') . ' - ' . $transactionNumber . ($description ? ' (' . $description . ')' : ''),
                'status' => 'posted',
                'created_by' => $user->id,
            ]);

            $entry->lines()->createMany([
                [
                    'chart_of_account_id' => $validated['account_id'],
                    'debit' => $isIn ? $validated['amount'] : 0,
                    'credit' => $isIn ? 0 : $validated['amount'],
                    'memo' => $isIn ? 'Kas/Bank masuk' : 'Kas/Bank keluar',
                ],
                [
                    'chart_of_account_id' => $validated['counter_account_id'],
                    'debit' => $isIn ? 0 : $validated['amount'],
                    'credit' => $isIn ? $validated['amount'] : 0,
                    'memo' => 'Transaksi ' . $transactionNumber,
                ],
            ]);
        });

        return to_route('apps.cash-bank.index')->with('success', 'Transaksi Kas & Bank berhasil dicatat, jurnal otomatis dibuat.');
    }

    private function cashAccounts()
    {
        return ChartOfAccount::where('is_header', false)
            ->where('is_active', true)
            ->where('type', 'asset')
            ->where('sub_type', 'current_asset')
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }
}