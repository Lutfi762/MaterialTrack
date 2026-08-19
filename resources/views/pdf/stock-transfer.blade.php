<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transfer Stok - {{ $transfer->reference_no }}</title>
    <style>
        @page { margin: 15mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.5; }
        .header { background: #3b82f6; color: white; padding: 20px 24px; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0 0; opacity: 0.85; font-size: 11px; }
        .ref-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; text-align: center; }
        .ref-box .ref { font-size: 14px; font-weight: 700; color: #1d4ed8; }
        .qty-box { text-align: center; margin: 16px 0; }
        .qty-box .number { font-size: 36px; font-weight: 700; color: #1d4ed8; }
        .qty-box .unit { font-size: 14px; color: #64748b; }
        .qty-box .item { font-size: 14px; color: #475569; margin-top: 4px; }
        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .detail-table td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
        .detail-table .label { font-size: 11px; font-weight: 600; color: #94a3b8; width: 40%; }
        .detail-table .value { font-size: 13px; font-weight: 600; color: #1e293b; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .notes { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px; margin-top: 16px; font-size: 12px; color: #92400e; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Transfer Stok Antar Departemen</h1>
        <p>MaterialTrack — Dicetak: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <div class="ref-box">
        <div class="ref">{{ $transfer->reference_no }}</div>
    </div>

    <div class="qty-box">
        <div class="number">{{ $transfer->qty }}</div>
        <div class="unit">{{ $transfer->unit }}</div>
        <div class="item">{{ $transfer->item->name ?? '-' }} ({{ $transfer->item->code ?? '-' }})</div>
    </div>

    <table class="detail-table">
        <tr>
            <td class="label">Status</td>
            <td class="value">
                <span class="badge badge-{{ $transfer->status }}">{{ ucfirst($transfer->status) }}</span>
            </td>
        </tr>
        <tr>
            <td class="label">Dari Departemen</td>
            <td class="value">{{ $transfer->fromDepartment->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Ke Departemen</td>
            <td class="value">{{ $transfer->toDepartment->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Dicatat Oleh</td>
            <td class="value">{{ $transfer->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td class="value">{{ $transfer->created_at }}</td>
        </tr>
        @if($transfer->approver)
        <tr>
            <td class="label">Disetujui Oleh</td>
            <td class="value">{{ $transfer->approver->name ?? '-' }}</td>
        </tr>
        @endif
        @if($transfer->approved_at)
        <tr>
            <td class="label">Waktu Persetujuan</td>
            <td class="value">{{ $transfer->approved_at }}</td>
        </tr>
        @endif
    </table>

    @if($transfer->notes)
    <div class="notes">
        <strong>Catatan:</strong> {{ $transfer->notes }}
    </div>
    @endif

    <div class="footer">
        MaterialTrack &copy; {{ date('Y') }} — Dokumen ini dihasilkan secara otomatis.
    </div>
</body>
</html>
