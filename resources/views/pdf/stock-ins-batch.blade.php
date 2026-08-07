<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Material Masuk</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11px; color: #1f2937; margin: 0; }
        .header { background: #059669; color: white; padding: 24px 30px; margin-bottom: 20px; }
        .header .brand { font-size: 12px; opacity: 0.85; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px; }
        .header h1 { margin: 0; font-size: 22px; font-weight: bold; }
        .header .sub { margin-top: 4px; font-size: 12px; opacity: 0.9; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data thead th { background: #1f2937; color: white; padding: 8px 7px; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.4px; }
        table.data tbody td { padding: 7px; border-bottom: 1px solid #e5e7eb; font-size: 10.5px; vertical-align: top; }
        table.data tbody tr:nth-child(even) { background: #f9fafb; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .item-name { font-weight: 600; color: #111827; }
        .item-code { color: #9ca3af; font-size: 9px; }
        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">MaterialTrack &mdash; Sistem Manajemen Stok &amp; Pengeluaran Material Gudang</div>
        <h1>Rekap Material Masuk</h1>
        <div class="sub">Total {{ $stockIns->count() }} transaksi</div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Ref</th>
                <th>Tanggal</th>
                <th>Material</th>
                <th>Department</th>
                <th class="text-end">Qty</th>
                <th>Sumber</th>
                <th>Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stockIns as $row)
                <tr>
                    <td>{{ $row->reference_no }}</td>
                    <td class="text-nowrap">{{ $row->date }}</td>
                    <td>
                        <div class="item-name">{{ $row->item->name ?? '-' }}</div>
                        <div class="item-code">{{ $row->item->code ?? '-' }}</div>
                    </td>
                    <td>{{ $row->department->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($row->qty) }}</td>
                    <td>{{ $row->source ?? '-' }}</td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Belum ada transaksi material masuk.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &mdash; MaterialTrack &copy; {{ now()->year }}
    </div>
</body>
</html>