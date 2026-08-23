<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinancialReportController extends Controller
{
    private const DEBIT_TYPES = ['asset', 'expense'];

    public function index(Request $request)
    {
        $from = $request->has('from') ? $request->input('from') : now()->startOfYear()->format('Y-m-d');
        $to = $request->has('to') ? $request->input('to') : now()->format('Y-m-d');
        $tab = in_array($request->input('tab'), ['trial-balance', 'income-statement', 'balance-sheet'], true)
            ? $request->input('tab')
            : 'trial-balance';

        $incomeStatement = $this->incomeStatement($from, $to);

        return Inertia::render('Apps/FinancialReports/Index', [
            'tab' => $tab,
            'from' => $from,
            'to' => $to,
            'trialBalance' => $this->trialBalance($to),
            'incomeStatement' => $incomeStatement,
            'balanceSheet' => $this->balanceSheet($to, $incomeStatement['net_income']),
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $tab = in_array($request->input('tab'), ['trial-balance', 'income-statement', 'balance-sheet'], true)
            ? $request->input('tab')
            : 'trial-balance';
        $from = $request->input('from', now()->startOfYear()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $incomeStatement = $this->incomeStatement($from, $to);
        $data = match ($tab) {
            'income-statement' => $incomeStatement,
            'balance-sheet' => $this->balanceSheet($to, $incomeStatement['net_income']),
            default => $this->trialBalance($to),
        };

        $pdf = Pdf::loadView('pdf.financial-report', [
            'reportType' => $tab,
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]);

        return $pdf->download('MaterialTrack-' . str_replace('-', ' ', ucwords($tab, '-')) . '-' . now()->format('Ymd-His') . '.pdf');
    }

    /** Netto (debit - credit) per akun pada rentang tanggal (status = posted). */
    private function movements(?string $from, ?string $to): array
    {
        $rows = DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('e.status', 'posted')
            ->when($from, fn ($q) => $q->whereDate('e.entry_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('e.entry_date', '<=', $to))
            ->groupBy('l.chart_of_account_id')
            ->selectRaw('l.chart_of_account_id, SUM(l.debit) AS dr, SUM(l.credit) AS cr')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[$row->chart_of_account_id] = (float) $row->dr - (float) $row->cr;
        }

        return $out;
    }

    private function accounts(): array
    {
        return ChartOfAccount::where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type', 'sub_type', 'opening_balance'])
            ->map(fn ($a) => [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'type' => $a->type,
                'sub_type' => $a->sub_type,
                'opening_balance' => (float) $a->opening_balance,
            ])
            ->toArray();
    }

    private function trialBalance(?string $to): array
    {
        $mov = $this->movements(null, $to);
        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($this->accounts() as $a) {
            $balance = $a['opening_balance'] + ($mov[$a['id']] ?? 0);

            // Balance tersimpan debit-positive; kolom = max(balance,0)/(max(-balance,0))
            $dr = max($balance, 0);
            $cr = max(-$balance, 0);

            $totalDebit += $dr;
            $totalCredit += $cr;

            $rows[] = [
                'code' => $a['code'],
                'name' => $a['name'],
                'type' => $a['type'],
                'debit' => round($dr, 2),
                'credit' => round($cr, 2),
            ];
        }

        return [
            'rows' => $rows,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
        ];
    }

    private function incomeStatement(?string $from, ?string $to): array
    {
        $mov = $this->movements($from, $to);
        $groups = [
            'operating_revenue' => [],
            'other_revenue' => [],
            'cost_of_goods_sold' => [],
            'operating_expense' => [],
            'other_expense' => [],
        ];

        foreach ($this->accounts() as $a) {
            if (! array_key_exists($a['sub_type'], $groups)) {
                continue;
            }
            $net = $mov[$a['id']] ?? 0;
            // Revenue & income bersaldo normal kredit: tampilkan sebagai nilai positif.
            $groups[$a['sub_type']][] = [
                'code' => $a['code'],
                'name' => $a['name'],
                'amount' => round(in_array($a['type'], self::DEBIT_TYPES, true) ? $net : -$net, 2),
            ];
        }

        $sum = fn ($g) => array_sum(array_column($g, 'amount'));

        $revenue = $sum($groups['operating_revenue']);
        $otherRevenue = $sum($groups['other_revenue']);
        $cogs = $sum($groups['cost_of_goods_sold']);
        $operatingExpense = $sum($groups['operating_expense']);
        $otherExpense = $sum($groups['other_expense']);

        $grossProfit = $revenue - $cogs;
        $operatingIncome = $grossProfit - $operatingExpense;
        $netIncome = $operatingIncome + $otherRevenue - $otherExpense;

        return [
            'sections' => [
                ['label' => 'Pendapatan Usaha', 'rows' => $groups['operating_revenue'], 'total' => $revenue],
                ['label' => 'Pendapatan Lainnya', 'rows' => $groups['other_revenue'], 'total' => $otherRevenue],
                ['label' => 'Harga Pokok Penjualan', 'rows' => $groups['cost_of_goods_sold'], 'total' => $cogs],
                ['label' => 'Beban Operasional', 'rows' => $groups['operating_expense'], 'total' => $operatingExpense],
                ['label' => 'Beban Lainnya', 'rows' => $groups['other_expense'], 'total' => $otherExpense],
            ],
            'gross_profit' => $grossProfit,
            'operating_income' => $operatingIncome,
            'net_income' => $netIncome,
        ];
    }

    private function balanceSheet(?string $to, float $netIncome): array
    {
        $mov = $this->movements(null, $to);
        $rows = [];
        $totals = ['asset' => 0.0, 'liability' => 0.0, 'equity' => $netIncome];

        foreach ($this->accounts() as $a) {
            // Akun laba-rugi tidak tampil di neraca; hasilnya terwakili lewat laba berjalan (net_income).
            if (! in_array($a['type'], ['asset', 'liability', 'equity'], true)) {
                continue;
            }
            $balance = $a['opening_balance'] + ($mov[$a['id']] ?? 0);
            // Normalisasi ke sisi normal (asset positif, liability/equity positif).
            $normalized = in_array($a['type'], self::DEBIT_TYPES, true) ? $balance : -$balance;
            $totals[$a['type']] += $normalized;
            $rows[] = [
                'type' => $a['type'],
                'code' => $a['code'],
                'name' => $a['name'],
                'amount' => round($normalized, 2),
            ];
        }

        return [
            'rows' => $rows,
            'totals' => [
                'asset' => round($totals['asset'], 2),
                'liability' => round($totals['liability'], 2),
                'equity' => round($totals['equity'], 2),
            ],
            'net_income' => $netIncome,
        ];
    }
}