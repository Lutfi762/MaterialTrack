<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #2d3748;
            margin: 0;
            padding: 24px 28px;
        }
        .header { border-bottom: 3px solid #198754; padding-bottom: 12px; margin-bottom: 14px; }
        .header table { width: 100%; }
        .brand-name { font-size: 19px; font-weight: bold; color: #198754; letter-spacing: 0.5px; }
        .brand-sub { font-size: 9px; color: #888; margin-top: 2px; }
        .doc-badge { background: #198754; color: white; font-size: 10px; font-weight: bold; padding: 6px 14px; border-radius: 3px; }
        .report-title { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 2px; }
        .report-period { text-align: center; font-size: 9.5px; color: #666; margin-bottom: 14px; }
        h3.section { font-size: 10.5px; margin: 14px 0 4px; color: #198754; text-transform: uppercase; letter-spacing: 0.5px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f0f9f4; color: #2f7a4f; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; padding: 6px 6px; border-bottom: 1px solid #b8e6c9; }
        td { padding: 5px 6px; border-bottom: 1px solid #eef2f7; }
        td.num { text-align: right; font-family: 'Courier New', monospace; }
        tr.total td { font-weight: bold; border-top: 1px solid #198754; border-bottom: 2px solid #198754; background: #f8fdf9; }
        tr.total td, tr.grand td { color: #1e6b44; }
        tr.grand td { font-weight: bold; border-top: 2px solid #198754; font-size: 11px; }
        .footer { text-align: center; font-size: 8px; color: #a0aec0; margin-top: 18px; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    @php $idr = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="brand-name">MaterialTrack</div>
                    <div class="brand-sub">Sistem Manajemen Stok &amp; Pengeluaran Material Gudang</div>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <span class="doc-badge">LAPORAN KEUANGAN</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        @switch($reportType)
            @case('income-statement') LAPORAN LABA RUGI @break
            @case('balance-sheet') NERACA (LAPORAN POSISI KEUANGAN) @break
            @default NERACA SALDO (TRIAL BALANCE)
        @endswitch
    </div>
    <div class="report-period">Periode: {{ $from }} s/d {{ $to }}</div>

    @if($reportType === 'trial-balance')
        <table>
            <tr>
                <th style="width:12%">Kode</th>
                <th>Akun</th>
                <th style="width:20%">Debit</th>
                <th style="width:20%">Kredit</th>
            </tr>
            @foreach($data['rows'] as $row)
                <tr>
                    <td>{{ $row['code'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="num">{{ $idr($row['debit']) }}</td>
                    <td class="num">{{ $idr($row['credit']) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="2">TOTAL</td>
                <td class="num">{{ $idr($data['total_debit']) }}</td>
                <td class="num">{{ $idr($data['total_credit']) }}</td>
            </tr>
        </table>
    @elseif($reportType === 'income-statement')
        @foreach($data['sections'] as $section)
            <h3 class="section">{{ $section['label'] }}</h3>
            <table>
                @foreach($section['rows'] as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="num">{{ $idr($row['amount']) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td>Total {{ $section['label'] }}</td>
                    <td class="num">{{ $idr($section['total']) }}</td>
                </tr>
            </table>
        @endforeach
        <table style="margin-top:14px">
            <tr><td>Laba Kotor (Gross Profit)</td><td class="num">{{ $idr($data['gross_profit']) }}</td></tr>
            <tr><td>Laba Usaha (Operating Income)</td><td class="num">{{ $idr($data['operating_income']) }}</td></tr>
            <tr class="grand"><td>LABA BERSIH PERIODE</td><td class="num">{{ $idr($data['net_income']) }}</td></tr>
        </table>
    @else
        @foreach(['asset' => 'ASET', 'liability' => 'KEWAJIBAN', 'equity' => 'EKUITAS'] as $type => $label)
            <h3 class="section">{{ $label }}</h3>
            <table>
                @foreach($data['rows'] as $row)
                    @if($row['type'] === $type)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="num">{{ $idr($row['amount']) }}</td>
                        </tr>
                    @endif
                @endforeach
                @if($type === 'equity')
                    <tr>
                        <td>Laba Berjalan (periode laporan)</td>
                        <td class="num">{{ $idr($data['net_income']) }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <td>Total {{ $label }}</td>
                    <td class="num">{{ $idr($data['totals'][$type]) }}</td>
                </tr>
            </table>
        @endforeach
    @endif

    <div class="footer">
        Dokumen ini dihasilkan otomatis oleh sistem MaterialTrack.
        <div style="margin-top:4px">Dicetak pada {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>