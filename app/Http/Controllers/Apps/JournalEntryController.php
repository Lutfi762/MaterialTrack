<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['creator', 'lines.account'])->latest();

        if ($request->filled('search')) {
            $query->where('entry_number', 'like', "%{$request->search}%");
        }

        return Inertia::render('Apps/JournalEntries/Index', [
            'journalEntries' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/JournalEntries/Create', [
            'accounts' => ChartOfAccount::where('is_header', false)
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'narration' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.memo' => 'nullable|string',
        ]);

        // Cek keseimbangan (Debit = Credit)
        $totalDebit = array_sum(array_column($validated['lines'], 'debit'));
        $totalCredit = array_sum(array_column($validated['lines'], 'credit'));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['lines' => 'Total Debit (' . number_format($totalDebit, 2) . ') harus sama dengan Total Kredit (' . number_format($totalCredit, 2) . ').']);
        }

        // Minimal satu baris debit > 0 dan satu baris kredit > 0 (baris kosong tidak berguna)
        $hasDebit = collect($validated['lines'])->contains(fn ($l) => (float) $l['debit'] > 0);
        $hasCredit = collect($validated['lines'])->contains(fn ($l) => (float) $l['credit'] > 0);

        if (! $hasDebit || ! $hasCredit) {
            return back()->withErrors(['lines' => 'Minimal satu baris debit > 0 dan satu baris kredit > 0 harus diisi.']);
        }

        // Tolak baris kosong (debit = kredit = 0) — baris tanpa nilai tidak berguna
        foreach ($validated['lines'] as $i => $line) {
            if ((float) $line['debit'] === 0.0 && (float) $line['credit'] === 0.0) {
                return back()->withErrors(["lines.{$i}" => 'Baris jurnal tidak boleh kosong (debit dan credit 0).']);
            }
        }

        DB::transaction(function () use ($validated) {
            $jNumber = DocumentNumber::next('journal_entries', 'JV');

            $entry = JournalEntry::create([
                'entry_number' => $jNumber,
                'entry_date' => $validated['entry_date'],
                'narration' => $validated['narration'] ?? null,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $line) {
                $entry->lines()->create([
                    'chart_of_account_id' => $line['chart_of_account_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'memo' => $line['memo'] ?? null,
                ]);
            }
        });

        return to_route('apps.journal-entries.index')->with('success', 'Jurnal umum (Journal Entry) berhasil diposting.');
    }

    public function show(JournalEntry $journalEntry)
    {
        return Inertia::render('Apps/JournalEntries/Show', [
            'journalEntry' => $journalEntry->load(['creator', 'lines.account']),
        ]);
    }
}
