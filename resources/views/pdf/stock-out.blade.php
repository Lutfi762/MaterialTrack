<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #2d3748;
            margin: 0;
            padding: 24px 28px;
        }
        .header {
            border-bottom: 3px solid #dc3545;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .header table { width: 100%; }
        .brand-name {
            font-size: 20px;
            font-weight: bold;
            color: #dc3545;
            letter-spacing: 0.5px;
        }
        .brand-sub {
            font-size: 9px;
            color: #888;
            margin-top: 2px;
        }
        .doc-badge {
            background: #dc3545;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 6px 14px;
            border-radius: 3px;
            letter-spacing: 0.5px;
        }
        .ref-strip {
            background: #fdf2f3;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 18px;
            text-align: center;
        }
        .ref-strip .ref-label {
            font-size: 8px;
            color: #a94454;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .ref-strip .ref-value {
            font-size: 17px;
            font-weight: bold;
            color: #dc3545;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        table.detail {
            width: 100%;
            border-collapse: collapse;
        }
        table.detail tr {
            border-bottom: 1px solid #f0f0f0;
        }
        table.detail td {
            padding: 9px 4px;
            vertical-align: top;
        }
        table.detail td.label {
            width: 32%;
            color: #888;
            font-size: 10px;
        }
        table.detail td.value {
            font-weight: bold;
            font-size: 11.5px;
            color: #2d3748;
        }
        .qty-highlight {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 4px;
            padding: 10px 14px;
            margin: 16px 0;
            text-align: center;
        }
        .qty-highlight .qty-number {
            font-size: 22px;
            font-weight: bold;
            color: #f57c00;
        }
        .qty-highlight .qty-unit {
            font-size: 11px;
            color: #a1701f;
        }
        .divider-dashed {
            border-top: 1px dashed #cbd5e0;
            margin: 18px 0 12px;
        }
        .footer {
            text-align: center;
            font-size: 8.5px;
            color: #a0aec0;
        }
        .footer .stamp {
            margin-top: 6px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="brand-name">MaterialTrack</div>
                    <div class="brand-sub">Sistem Manajemen Stok &amp; Pengeluaran Material Gudang</div>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <span class="doc-badge">MATERIAL KELUAR</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="ref-strip">
        <div class="ref-label">Nomor Referensi Transaksi</div>
        <div class="ref-value">{{ $stockOut->reference_no }}</div>
    </div>

    <div class="qty-highlight">
        <span class="qty-number">{{ $stockOut->qty }}</span>
        <span class="qty-unit">{{ $stockOut->item->unit ?? 'unit' }}</span>
        <div style="font-size: 10px; color: #a1701f; margin-top: 3px;">{{ $stockOut->item->name ?? '-' }}</div>
    </div>

    <table class="detail">
        <tr>
            <td class="label">Tanggal Transaksi</td>
            <td class="value">{{ \Carbon\Carbon::parse($stockOut->date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Kode Material</td>
            <td class="value">{{ $stockOut->item->code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Departemen</td>
            <td class="value">{{ $stockOut->department->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">PIC Pengambil</td>
            <td class="value">{{ $stockOut->pic->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Keperluan</td>
            <td class="value">{{ $stockOut->purpose }}</td>
        </tr>
        @if($stockOut->notes)
        <tr>
            <td class="label">Catatan</td>
            <td class="value">{{ $stockOut->notes }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Dicatat Oleh</td>
            <td class="value">{{ $stockOut->user->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <div class="footer">
        <div>Dokumen ini dihasilkan otomatis oleh sistem MaterialTrack sebagai pengganti struk fisik.</div>
        <div class="stamp">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>
</body>
</html>