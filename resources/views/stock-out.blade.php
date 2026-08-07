<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 12px; margin-bottom: 16px; }
        .header h2 { margin: 0; color: #0d6efd; }
        .header p { margin: 2px 0; font-size: 10px; color: #666; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; color: white; background: #dc3545; }
        table.info { width: 100%; margin-top: 16px; border-collapse: collapse; }
        table.info td { padding: 6px 4px; vertical-align: top; }
        table.info td.label { width: 35%; color: #666; }
        table.info td.value { font-weight: bold; }
        .divider { border-top: 1px dashed #ccc; margin: 14px 0; }
        .footer { text-align: center; margin-top: 24px; font-size: 10px; color: #999; }
        .ref-box { background: #f8f9fa; padding: 10px; border-radius: 6px; text-align: center; margin-bottom: 16px; }
        .ref-box .ref-no { font-size: 16px; font-weight: bold; color: #0d6efd; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>MaterialTrack</h2>
        <p>Sistem Manajemen Stok & Pengeluaran Material Gudang</p>
        <span class="badge">BUKTI MATERIAL KELUAR</span>
    </div>

    <div class="ref-box">
        <div style="font-size: 10px; color: #666;">No. Referensi</div>
        <div class="ref-no">{{ $stockOut->reference_no }}</div>
    </div>

    <table class="info">
        <tr>
            <td class="label">Tanggal</td>
            <td class="value">{{ \Carbon\Carbon::parse($stockOut->date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Material</td>
            <td class="value">{{ $stockOut->item->name }} ({{ $stockOut->item->code }})</td>
        </tr>
        <tr>
            <td class="label">Quantity</td>
            <td class="value">{{ $stockOut->qty }} {{ $stockOut->item->unit }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td class="value">{{ $stockOut->department->name }}</td>
        </tr>
        <tr>
            <td class="label">PIC Pengambil</td>
            <td class="value">{{ $stockOut->pic->name }}</td>
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
            <td class="value">{{ $stockOut->user->name }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer">
        <p>Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
        <p>Dokumen ini dihasilkan otomatis oleh sistem MaterialTrack</p>
    </div>
</body>
</html>