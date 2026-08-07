<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Opname {{ $opname->reference_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #1f2937; margin: 0; }

        .header {
            background: #2563eb;
            color: white;
            padding: 24px 30px;
            margin-bottom: 24px;
        }
        .header .brand { font-size: 12px; opacity: 0.85; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: bold; }
        .header .ref { margin-top: 4px; font-size: 13px; opacity: 0.95; font-weight: 600; }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        table.detail { width: 100%; border-collapse: collapse; }
        table.detail tr:nth-child(even) { background: #f9fafb; }
        table.detail th, table.detail td { padding: 10px 16px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        table.detail th { width: 38%; color: #6b7280; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; }
        table.detail td { font-size: 13px; color: #111827; font-weight: 500; }

        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
            font-size: 12px;
        }
        .badge-success { background: #10b981; }
        .badge-info { background: #3b82f6; }
        .badge-danger { background: #ef4444; }

        .stock-compare {
            display: table;
            width: 100%;
            margin: 16px 0;
        }
        .stock-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 16px 10px;
            border: 1px solid #e5e7eb;
        }
        .stock-box .label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .stock-box .value { font-size: 22px; font-weight: bold; margin-top: 6px; }

        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">MaterialTrack &mdash; Sistem Manajemen Stok &amp; Pengeluaran Material Gudang</div>
        <h1>Bukti Stock Opname</h1>
        <div class="ref">{{ $opname->reference_no }}</div>
    </div>

    <div class="stock-compare">
        <div class="stock-box">
            <div class="label">Stok Sistem</div>
            <div class="value">{{ number_format($opname->system_stock) }}</div>
        </div>
        <div class="stock-box">
            <div class="label">Stok Fisik</div>
            <div class="value">{{ number_format($opname->physical_stock) }}</div>
        </div>
        <div class="stock-box">
            <div class="label">Selisih</div>
            <div class="value">
                @if ($opname->difference === 0)
                    <span class="badge badge-success">Sesuai</span>
                @elseif ($opname->difference > 0)
                    <span class="badge badge-info">+{{ $opname->difference }}</span>
                @else
                    <span class="badge badge-danger">{{ $opname->difference }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <table class="detail">
            <tr><th>Periode</th><td>{{ \Carbon\Carbon::createFromFormat('Y-m', $opname->period)->translatedFormat('F Y') }}</td></tr>
            <tr><th>Tanggal Opname</th><td>{{ \Carbon\Carbon::parse($opname->date)->translatedFormat('d F Y') }}</td></tr>
            <tr><th>Kode Material</th><td>{{ $opname->item->code ?? '-' }}</td></tr>
            <tr><th>Nama Material</th><td>{{ $opname->item->name ?? '-' }}</td></tr>
            <tr><th>Kategori</th><td>{{ $opname->item->category->name ?? '-' }}</td></tr>
            <tr><th>Satuan</th><td>{{ $opname->item->unit ?? '-' }}</td></tr>
            <tr><th>Catatan</th><td>{{ $opname->notes ?? '-' }}</td></tr>
            <tr><th>Dicatat Oleh</th><td>{{ $opname->user->name ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &mdash; MaterialTrack &copy; {{ now()->year }}
    </div>
</body>
</html>