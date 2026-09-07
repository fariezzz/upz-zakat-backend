<?php

namespace App\Exports;

use App\Models\Muzakki;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MuzakkiExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths, WithColumnFormatting
{
    protected $search;
    protected $kategori;
    protected $rowNumber = 1;

    public function __construct($search = null, $kategori = null)
    {
        $this->search = $search;
        $this->kategori = $kategori;
    }

    /**
     * Ambil data Muzakki dengan pengurutan:
     * 1. Dosen/Staff dulu
     * 2. Umum di bawahnya
     */
    public function collection()
    {
        $query = Muzakki::where('tipe_muzakki', 'terdaftar');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $search = $this->search;
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('nik', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('no_hp', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%")
                  ->orWhere('pekerjaan', 'ilike', "%{$search}%")
                  ->orWhere('kategori', 'ilike', "%{$search}%");
            });
        }

        // Apply kategori filter
        if ($this->kategori) {
            if ($this->kategori === 'dosen_staf' || $this->kategori === 'unsil') {
                $query->where(function ($q) {
                    $q->where('kategori', 'ilike', '%Dosen%')
                      ->orWhere('kategori', 'ilike', '%Staf%')
                      ->orWhere('kategori', 'ilike', '%Civitas%')
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('unit_kerja')
                             ->where('unit_kerja', '!=', '')
                             ->where('unit_kerja', '!=', 'Masyarakat Umum')
                             ->where('unit_kerja', '!=', 'Umum');
                      });
                });
            } elseif ($this->kategori === 'umum') {
                $query->where(function ($q) {
                    $q->where('kategori', 'ilike', '%Umum%')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('unit_kerja')
                             ->orWhere('unit_kerja', '')
                             ->orWhere('unit_kerja', 'Masyarakat Umum')
                             ->orWhere('unit_kerja', 'Umum');
                      });
                });
            }
        }

        // Ambil semua data
        $allMuzakki = $query->get();

        // Pisahkan Dosen/Staff dan Umum
        $dosenStaf = $allMuzakki->filter(function ($m) {
            return (!empty($m->kategori) && 
                   (stripos($m->kategori, 'Dosen') !== false || 
                    stripos($m->kategori, 'Staf') !== false || 
                    stripos($m->kategori, 'UNSIL') !== false))
                || (!empty($m->unit_kerja) && 
                    !in_array($m->unit_kerja, ['Masyarakat Umum', 'Umum', '', null]));
        })->sortBy('nama');

        $umum = $allMuzakki->filter(function ($m) {
            return (empty($m->kategori) || stripos($m->kategori, 'Umum') !== false)
                || (empty($m->unit_kerja) || 
                    in_array($m->unit_kerja, ['Masyarakat Umum', 'Umum', '']));
        })->sortBy('nama');

        // Gabungkan: Dosen/Staff dulu, lalu Umum
        return $dosenStaf->concat($umum)->values();
    }

    /**
     * Header kolom Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Kategori',
            'NIK',
            'NIP',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Pekerjaan',
            'Unit Kerja / Fakultas',
            'Alamat Lengkap',
            'Email',
            'No. HP / WA',
            'Jenis Zakat',
            'Frekuensi',
            'Nominal (Rp)',
            'Metode Pembayaran',
            'Bank',
            'E-Wallet',
            'Tanggal Daftar',
        ];
    }

    /**
     * Mapping data per baris
     */
    public function map($muzakki): array
    {
        // Determine kategori
        $isUnsil = (!empty($muzakki->kategori) && 
                   (stripos($muzakki->kategori, 'Dosen') !== false || 
                    stripos($muzakki->kategori, 'Staf') !== false || 
                    stripos($muzakki->kategori, 'UNSIL') !== false))
            || (!empty($muzakki->unit_kerja) && 
                !in_array($muzakki->unit_kerja, ['Masyarakat Umum', 'Umum', '']));

        $kategoriLabel = $isUnsil ? 'Dosen & Staf UNSIL' : 'Muzakki Umum';

        // Format tanggal daftar
        $tanggalDaftar = $muzakki->created_at 
            ? $muzakki->created_at->format('d/m/Y H:i') 
            : '-';

        return [
            $this->rowNumber++,
            $muzakki->nama ?? '-',
            $kategoriLabel,
            $muzakki->nik ?? '-',          // Format TEXT via columnFormats()
            $muzakki->nip ?? '-',          // Format TEXT via columnFormats()
            $muzakki->jenis_kelamin ?? '-',
            $muzakki->tempat_lahir ?? '-',
            $muzakki->tanggal_lahir ?? '-',
            $muzakki->pekerjaan ?? '-',
            $muzakki->unit_kerja ?? '-',
            $muzakki->alamat_lengkap ?? '-',
            $muzakki->email ?? '-',
            $muzakki->no_hp ?? '-',        // Format TEXT via columnFormats()
            $muzakki->jenis_zakat ?? '-',
            $muzakki->frekuensi ?? '-',
            $muzakki->nominal ?? 0,        // Angka asli, format #,##0 via columnFormats()
            $muzakki->metode_pembayaran ?? '-',
            $muzakki->pilihan_bank ?? '-',
            $muzakki->pilihan_ewallet ?? '-',
            $tanggalDaftar,
        ];
    }

    /**
     * Nama sheet
     */
    public function title(): string
    {
        return 'Data Muzakki';
    }

    /**
     * Format kolom: NIK, NIP, No HP sebagai TEXT; Nominal sebagai angka
     */
    public function columnFormats(): array
    {
        return [
            'D' => '0',                                // NIK — number tanpa desimal
            'E' => '0',                                // NIP — number tanpa desimal
            'M' => NumberFormat::FORMAT_TEXT,           // No. HP / WA
            'P' => '#,##0',                            // Nominal (Rp) — angka lengkap dengan separator
        ];
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Style header
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // Row height header
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Wrap text + vertical top untuk SEMUA data cell
        $sheet->getStyle("A2:T{$highestRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A2:T{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        // Border untuk semua data
        $sheet->getStyle("A1:T{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Kolom No: center
        $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Kolom Nominal: right-align
        $sheet->getStyle("P2:P{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 30,  // Nama
            'C' => 22,  // Kategori
            'D' => 22,  // NIK (diperlebar)
            'E' => 24,  // NIP (diperlebar)
            'F' => 16,  // Jenis Kelamin
            'G' => 18,  // Tempat Lahir
            'H' => 16,  // Tanggal Lahir
            'I' => 22,  // Pekerjaan
            'J' => 38,  // Unit Kerja
            'K' => 42,  // Alamat
            'L' => 28,  // Email
            'M' => 18,  // No HP
            'N' => 28,  // Jenis Zakat
            'O' => 16,  // Frekuensi
            'P' => 22,  // Nominal (diperlebar)
            'Q' => 20,  // Metode
            'R' => 14,  // Bank
            'S' => 14,  // E-Wallet
            'T' => 20,  // Tanggal Daftar
        ];
    }
}
