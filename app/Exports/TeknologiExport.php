<?php

namespace App\Exports;

use App\Models\Teknologi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeknologiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Teknologi::all();
    }

    public function map($teknologi): array
    {
        return [
            $teknologi->id,
            $teknologi->nama,
            $teknologi->slug,
            $teknologi->kategori_teknologi, // Framework, Language, dll
            $teknologi->ikon_url,
            $teknologi->status_aktif ? 'Aktif' : 'Nonaktif',
            $teknologi->created_at->format('d-m-Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Teknologi',
            'Slug',
            'Jenis/Kategori',
            'URL Ikon',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}