<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Report - MaterialTrack</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.5; }
        .header { background: #7c3aed; color: white; padding: 20px 24px; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; opacity: 0.85; font-size: 11px; }
        .kpi-grid { display: flex; gap: 12px; margin-bottom: 20px; }
        .kpi-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; text-align: center; }
        .kpi-box .number { font-size: 28px; font-weight: 700; color: #7c3aed; }
        .kpi-box .label { font-size: 10px; color: #64748b; text-transform: uppercase; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #7c3aed; padding-bottom: 6px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { background: #1f2937; color: white; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .period { font-size: 12px; color: #475569; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MaterialTrack Dashboard Report</h1>
        <p>Laporan Ringkasan Inventaris - Dicetak: {{ now()->format('d M Y H:i') }}</p>
    </div>

    @if($dateFrom && $dateTo)
    <div class="period">Periode: {{ $dateFrom }} s/d {{ $dateTo }}</div>
    @endif

    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="number">{{ $totalItems }}</div>
            <div class="label">Total Material</div>
        </div>
        <div class="kpi-box">
            <div class="number" style="color: #16a34a;">{{ $availableStock }}</div>
            <div class="label">Stok Aman</div>
        </div>
        <div class="kpi-box">
            <div class="number" style="color: #f59e0b;">{{ $lowStock }}</div>
            <div class="label">Stok Menipis</div>
        </div>
        <div class="kpi-box">
            <div class="number" style="color: #dc2626;">{{ $outOfStock }}</div>
            <div class="label">Stok Habis</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Transaksi</div>
        <table>
            <thead>
                <tr>
                    <th>Jenis Transaksi</th>
                    <th style="text-align:right">Jumlah</th>
                    <th style="text-align:right">Total Qty</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-success">Masuk</span></td>
                    <td style="text-align:right">{{ $stockInCount }}</td>
                    <td style="text-align:right">{{ $stockInQty }} unit</td>
                </tr>
                <tr>
                    <td><span class="badge badge-danger">Keluar</span></td>
                    <td style="text-align:right">{{ $stockOutCount }}</td>
                    <td style="text-align:right">{{ $stockOutQty }} unit</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">Menunggu Approval</span></td>
                    <td style="text-align:right">{{ $pendingApproval }}</td>
                    <td style="text-align:right">-</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(isset($lowStockItems) && count($lowStockItems) > 0)
    <div class="section">
        <div class="section-title">Material Stok Menipis</div>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th style="text-align:right">Stok</th>
                    <th style="text-align:right">Min. Stok</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockItems as $item)
                <tr>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td style="text-align:right">{{ $item->current_stock }} {{ $item->unit }}</td>
                    <td style="text-align:right">{{ $item->min_stock }}</td>
                    <td>
                        @if($item->current_stock <= 0)
                            <span class="badge badge-danger">Habis</span>
                        @else
                            <span class="badge badge-warning">Menipis</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($topMaterials) && count($topMaterials) > 0)
    <div class="section">
        <div class="section-title">Top 5 Material Paling Sering Keluar (7 Hari Terakhir)</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Material</th>
                    <th style="text-align:right">Total Keluar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topMaterials as $index => $m)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $m->name }}</td>
                    <td style="text-align:right">{{ $m->total }} unit</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        MaterialTrack &copy; {{ date('Y') }} — Laporan ini dihasilkan secara otomatis.
    </div>
</body>
</html>
