<?php

namespace App\Exports;

use App\Models\KategoriProyek;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KategoriExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return KategoriProyek::all();
    }

    public function map($kategori): array
    {
        return [
            $kategori->id,
            $kategori->nama,
            $kategori->slug,
            $kategori->deskripsi ?? '-',
            $kategori->urutan,
            $kategori->status_aktif ? 'Aktif' : 'Nonaktif',
            $kategori->created_at->format('d-m-Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Kategori',
            'Slug',
            'Deskripsi',
            'Urutan',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1    => ['font' => ['bold' => true]],
        ];
    }
}