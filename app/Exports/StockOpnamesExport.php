<?php

namespace App\Exports;

use App\Models\StockOpname;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockOpnamesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected Builder $query;
    protected int $rowCount = 0;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with(['item.category', 'user']);
    }

    public function headings(): array
    {
        return [
            'No. Referensi',
            'Periode',
            'Tanggal',
            'Kode Material',
            'Nama Material',
            'Kategori',
            'Stok Sistem',
            'Stok Fisik',
            'Selisih',
            'Status',
            'Catatan',
            'Oleh',
        ];
    }

    public function map($opname): array
    {
        $this->rowCount++;

        $status = $opname->difference === 0
            ? 'Sesuai'
            : ($opname->difference > 0 ? 'Surplus' : 'Defisit');

        return [
            $opname->reference_no,
            $opname->period,
            \Carbon\Carbon::parse($opname->date)->format('d/m/Y'),
            $opname->item->code ?? '-',
            $opname->item->name ?? '-',
            $opname->item->category->name ?? '-',
            $opname->system_stock,
            $opname->physical_stock,
            $opname->difference,
            $status,
            $opname->notes ?? '-',
            $opname->user->name ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, // Ref
            'B' => 10, // Periode
            'C' => 12, // Tanggal
            'D' => 14, // Kode
            'E' => 28, // Nama
            'F' => 14, // Kategori
            'G' => 12, // Stok Sistem
            'H' => 12, // Stok Fisik
            'I' => 10, // Selisih
            'J' => 12, // Status
            'K' => 25, // Catatan
            'L' => 18, // Oleh
        ];
    }

    public function styles($sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(24);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->rowCount + 1;

                // Freeze header row
                $sheet->freezePane('A2');

                // Border seluruh tabel
                $sheet->getStyle("A1:L{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // Align angka ke kanan (kolom Stok Sistem, Stok Fisik, Selisih)
                $sheet->getStyle("G2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Zebra striping + pewarnaan status per baris
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:L{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F9FAFB');
                    }

                    $status = $sheet->getCell("J{$row}")->getValue();
                    $color = match ($status) {
                        'Surplus' => '3B82F6',
                        'Defisit' => 'EF4444',
                        default   => '10B981',
                    };

                    $sheet->getStyle("J{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => $color]],
                    ]);
                }

                // Auto height untuk semua baris
                for ($row = 1; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
            },
        ];
    }
}