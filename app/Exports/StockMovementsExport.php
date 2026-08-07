<?php

namespace App\Exports;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockMovementsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected Builder $query;
    protected int $rowCount = 0;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with(['item.category', 'user', 'pic']);
    }

    public function headings(): array
    {
        return [
            'Tanggal', 'Kode Material', 'Nama Material', 'Kategori',
            'Tipe', 'Qty', 'Stok Sebelum', 'Stok Sesudah', 'Keterangan', 'Oleh',
        ];
    }

    public function map($mov): array
    {
        $this->rowCount++;

        $tipe = match ($mov->type) {
            'in'  => 'Masuk',
            'out' => 'Keluar',
            default => 'Adjustment',
        };

        return [
            $mov->created_at,
            $mov->item->code ?? '-',
            $mov->item->name ?? '-',
            $mov->item->category->name ?? '-',
            $tipe,
            $mov->qty,
            $mov->stock_before ?? '-',
            $mov->balance_after ?? '-',
            $mov->notes ?? '-',
            $mov->pic->name ?? $mov->user->name ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 14, 'C' => 28, 'D' => 14,
            'E' => 10, 'F' => 10, 'G' => 12, 'H' => 12, 'I' => 22, 'J' => 18,
        ];
    }

    public function styles($sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
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

                $sheet->freezePane('A2');

                $sheet->getStyle("A1:J{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
                ]);

                $sheet->getStyle("F2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:J{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F9FAFB');
                    }

                    $tipe = $sheet->getCell("E{$row}")->getValue();
                    $color = match ($tipe) {
                        'Masuk' => '10B981',
                        'Keluar' => 'EF4444',
                        default => 'F59E0B',
                    };
                    $sheet->getStyle("E{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => $color]],
                    ]);
                }
            },
        ];
    }
}