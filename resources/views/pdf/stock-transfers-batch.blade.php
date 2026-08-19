<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Transfer Stok</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; }
        .header { background: #3b82f6; color: white; padding: 20px 24px; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0 0; opacity: 0.85; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { background: #1f2937; color: white; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekap Transfer Stok</h1>
        <p>MaterialTrack — Dicetak: {{ now()->format('d M Y H:i') }} | Total: {{ $transfers->count() }} transfer</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Referensi</th>
                <th>Material</th>
                <th>Dari</th>
                <th>Ke</th>
                <th style="text-align:right">Qty</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $t)
            <tr>
                <td>{{ $t->reference_no }}</td>
                <td>{{ $t->item->name ?? '-' }}</td>
                <td>{{ $t->fromDepartment->name ?? '-' }}</td>
                <td>{{ $t->toDepartment->name ?? '-' }}</td>
                <td style="text-align:right">{{ $t->qty }} {{ $t->unit }}</td>
                <td><span class="badge badge-{{ $t->status }}">{{ ucfirst($t->status) }}</span></td>
                <td>{{ $t->created_at }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center">Tidak ada data transfer.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        MaterialTrack &copy; {{ date('Y') }} — Dokumen ini dihasilkan secara otomatis.
    </div>
</body>
</html>
