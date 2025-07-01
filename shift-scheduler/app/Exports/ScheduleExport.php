<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ScheduleExport implements FromCollection, WithHeadings, WithStyles
{
    protected $schedules;
    
    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }
    
    public function collection()
    {
        return $this->schedules->map(function ($schedule) {
            return [
                'Date' => $schedule->date->format('Y-m-d'),
                'Day' => $schedule->date->format('l'),
                'Person' => $schedule->person->name,
                'Shift Type' => $schedule->shift_type,
                'Shift Time' => $schedule->shift_time,
                'Status' => ucfirst(str_replace('_', ' ', $schedule->status)),
            ];
        });
    }
    
    public function headings(): array
    {
        return [
            'Date',
            'Day',
            'Person',
            'Shift Type',
            'Shift Time',
            'Status',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
            ],
        ];
    }
}