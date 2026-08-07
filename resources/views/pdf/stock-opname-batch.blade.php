<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Opname {{ $period }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11px; color: #1f2937; margin: 0; }

        .header {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            background: #2563eb;
            color: white;
            padding: 24px 30px;
            margin-bottom: 20px;
        }
        .header .brand { font-size: 12px; opacity: 0.85; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px; }
        .header h1 { margin: 0; font-size: 22px; font-weight: bold; }
        .header .sub { margin-top: 4px; font-size: 12px; opacity: 0.9; }

        .summary {
            display: table;
            width: 100%;
            margin-bottom: 18px;
        }
        .summary-box {
            display: table-cell;
            width: 25%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        .summary-box .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-box .value { font-size: 20px; font-weight: bold; margin-top: 2px; }
        .value-total { color: #2563eb; }
        .value-sesuai { color: #059669; }
        .value-surplus { color: #2563eb; }
        .value-defisit { color: #dc2626; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data thead th {
            background: #1f2937;
            color: white;
            padding: 8px 7px;
            text-align: left;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        table.data tbody td {
            padding: 7px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10.5px;
            vertical-align: top;
        }
        table.data tbody tr:nth-child(even) { background: #f9fafb; }

        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .item-name { font-weight: 600; color: #111827; }
        .item-code { color: #9ca3af; font-size: 9px; }

        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
            font-size: 9px;
        }
        .badge-success { background: #10b981; }
        .badge-info { background: #3b82f6; }
        .badge-danger { background: #ef4444; }

        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">MaterialTrack &mdash; Sistem Manajemen Stok &amp; Pengeluaran Material Gudang</div>
        <h1>Rekap Stock Opname</h1>
        <div class="sub">Periode {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y') }}</div>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Item</div>
            <div class="value value-total">{{ $opnames->count() }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Sesuai</div>
            <div class="value value-sesuai">{{ $opnames->where('difference', 0)->count() }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Surplus</div>
            <div class="value value-surplus">{{ $opnames->where('difference', '>', 0)->count() }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Defisit</div>
            <div class="value value-defisit">{{ $opnames->where('difference', '<', 0)->count() }}</div>
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Ref</th>
                <th>Tanggal</th>
                <th>Material</th>
                <th>Kategori</th>
                <th class="text-end">Stok Sistem</th>
                <th class="text-end">Stok Fisik</th>
                <th class="text-center">Selisih</th>
                <th>Catatan</th>
                <th>Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($opnames as $op)
                <tr>
                    <td>{{ $op->reference_no }}</td>
                    <td>{{ \Carbon\Carbon::parse($op->date)->format('d M Y') }}</td>
                    <td>
                        <div class="item-name">{{ $op->item->name ?? '-' }}</div>
                        <div class="item-code">{{ $op->item->code ?? '-' }}</div>
                    </td>
                    <td>{{ $op->item->category->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($op->system_stock) }}</td>
                    <td class="text-end">{{ number_format($op->physical_stock) }}</td>
                    <td class="text-center">
                        @if ($op->difference === 0)
                            <span class="badge badge-success">Sesuai</span>
                        @elseif ($op->difference > 0)
                            <span class="badge badge-info">+{{ $op->difference }}</span>
                        @else
                            <span class="badge badge-danger">{{ $op->difference }}</span>
                        @endif
                    </td>
                    <td>{{ $op->notes ?? '-' }}</td>
                    <td>{{ $op->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">Belum ada data opname untuk periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &mdash; MaterialTrack &copy; {{ now()->year }}
    </div>
</body>
</html>