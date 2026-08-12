<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeduplicateMuzakki extends Command
{
    protected $signature   = 'muzakki:deduplicate {--dry-run : Tampilkan saja tanpa hapus}';
    protected $description = 'Hapus data muzakki duplikat — simpan ID terkecil per nama';

    public function handle(): void
    {
        // Temukan nama yang muncul lebih dari 1 kali
        $dupes = DB::table('muzakki')
            ->select('nama', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('nama')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($dupes->isEmpty()) {
            $this->info('✓ Tidak ada data duplikat.');
            return;
        }

        $this->warn("Ditemukan {$dupes->count()} nama duplikat:");

        $totalHapus = 0;
        foreach ($dupes as $d) {
            $this->line("  • {$d->nama} ({$d->count}x) → simpan id={$d->keep_id}");
            $totalHapus += $d->count - 1;
        }

        $this->newLine();
        $this->line("Total akan dihapus: {$totalHapus} baris");

        if ($this->option('dry-run')) {
            $this->warn('Mode dry-run, tidak ada yang dihapus.');
            return;
        }

        // Hapus semua kecuali ID terkecil per nama
        foreach ($dupes as $d) {
            DB::table('muzakki')
                ->where('nama', $d->nama)
                ->where('id', '!=', $d->keep_id)
                ->delete();
        }

        $this->info("✓ {$totalHapus} data duplikat berhasil dihapus.");
    }
}
