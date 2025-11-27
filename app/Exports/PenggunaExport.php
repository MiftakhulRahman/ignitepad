<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery; // Ganti ke FromQuery biar lebih fleksibel
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenggunaExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $role;

    public function __construct($role = null)
    {
        $this->role = $role;
    }

    public function query()
    {
        $query = User::query()->with(['prodi', 'perans']);

        if ($this->role && $this->role !== 'semua') {
            $query->whereHas('perans', fn($q) => $q->where('slug', $this->role));
        }

        return $query;
    }

    public function map($user): array
    {
        $roleSlug = $user->perans->first()?->slug;
        
        // Logika Identitas: Jika Mhs ambil NIM, Jika Dosen ambil NIDN
        $identitas = '-';
        if ($roleSlug === 'mahasiswa') $identitas = $user->nim;
        elseif ($roleSlug === 'dosen') $identitas = $user->nidn;

        return [
            $user->id,
            $user->nama,
            $user->email,
            ucfirst($user->perans->first()?->nama ?? '-'),
            $identitas, // Kolom Dinamis
            $user->prodi?->nama ?? '-',
            $user->status_aktif ? 'Aktif' : 'Nonaktif',
            $user->created_at->format('d-m-Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID', 'Nama Lengkap', 'Email', 'Peran', 'NIM/NIDN', 'Program Studi', 'Status', 'Tanggal Daftar'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [ 1 => ['font' => ['bold' => true]] ];
    }
}