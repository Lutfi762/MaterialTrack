<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockOutsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function title(): string
    {
        return 'Riwayat Material Keluar';
    }

    public function collection()
    {
        return $this->query->with(['item', 'department', 'pic', 'user'])->get();
    }

    public function headings(): array
    {
        return [
            'No. Referensi',
            'Tanggal',
            'Material',
            'Kode Material',
            'Qty',
            'Satuan',
            'Department',
            'PIC',
            'Keperluan',
            'Catatan',
            'Dicatat Oleh',
        ];
    }

    public function map($row): array
    {
        return [
            $row->reference_no,
            \Carbon\Carbon::parse($row->date)->format('d/m/Y'),
            $row->item->name ?? '-',
            $row->item->code ?? '-',
            $row->qty,
            $row->item->unit ?? '-',
            $row->department->name ?? '-',
            $row->pic->name ?? '-',
            $row->purpose,
            $row->notes ?? '-',
            $row->user->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Header row: merah, bold, putih, rata tengah
        $sheet->getStyle('A1:' . $highestCol . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DC3545'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Border tipis di seluruh tabel data
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
            ],
        ]);

        // Selang-seling warna baris (zebra stripe)
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':' . $highestCol . $row)->applyFromArray([
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FDF2F3'],
                    ],
                ]);
            }
        }

        // Qty column rata tengah
        $sheet->getStyle('E2:E' . $highestRow)->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }

    public function columnFormats(): array
    {
        return [];
    }
}