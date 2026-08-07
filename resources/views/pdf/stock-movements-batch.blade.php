<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Riwayat Pergerakan Stok</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11px; color: #1f2937; margin: 0; }

        .header {
            background: #2563eb;
            color: white;
            padding: 24px 30px;
            margin-bottom: 20px;
        }
        .header .brand { font-size: 12px; opacity: 0.85; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px; }
        .header h1 { margin: 0; font-size: 22px; font-weight: bold; }
        .header .sub { margin-top: 4px; font-size: 12px; opacity: 0.9; }

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
        .badge-in { background: #10b981; }
        .badge-out { background: #ef4444; }

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
        <h1>Riwayat Pergerakan Stok</h1>
        <div class="sub">Total {{ $movements->count() }} transaksi</div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Material</th>
                <th>Tipe</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Stok Sebelum</th>
                <th class="text-end">Stok Sesudah</th>
                <th>Keterangan</th>
                <th>Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $mov)
                <tr>
                    <td class="text-nowrap">{{ $mov->created_at }}</td>
                    <td>
                        <div class="item-name">{{ $mov->item->name ?? '-' }}</div>
                        <div class="item-code">{{ $mov->item->code ?? '-' }}</div>
                    </td>
                    <td>
                        @if ($mov->type === 'in')
                            <span class="badge badge-in">Masuk</span>
                        @elseif ($mov->type === 'out')
                            <span class="badge badge-out">Keluar</span>
                        @else
                            <span class="badge" style="background:#f59e0b;">Adjustment</span>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($mov->qty) }}</td>
                    <td class="text-end">{{ $mov->stock_before ?? '-' }}</td>
                    <td class="text-end">{{ $mov->balance_after ?? '-' }}</td>
                    <td>{{ $mov->notes ?? '-' }}</td>
                    <td>{{ $mov->pic->name ?? $mov->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada riwayat pergerakan stok.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &mdash; MaterialTrack &copy; {{ now()->year }}
    </div>
</body>
</html>