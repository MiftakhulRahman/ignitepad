<?php

namespace App\Exports;

use App\Models\ProgramStudi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProdiExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $fakultas;

    public function __construct($fakultas = null)
    {
        $this->fakultas = $fakultas;
    }

    public function query()
    {
        $query = ProgramStudi::query();

        if ($this->fakultas && $this->fakultas !== 'semua') {
            $query->where('fakultas', $this->fakultas);
        }

        return $query;
    }

    public function map($prodi): array
    {
        return [
            $prodi->id,
            $prodi->kode,
            $prodi->nama,
            $prodi->fakultas,
            $prodi->created_at->format('d-m-Y'),
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Kode Prodi', 'Nama Program Studi', 'Fakultas', 'Tanggal Dibuat'];
    }

    public function styles(Worksheet $sheet)
    {
        return [ 1 => ['font' => ['bold' => true]] ];
    }
}