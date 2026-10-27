<?php

namespace Database\Seeders;

use App\Http\Controllers\PengurusController;
use App\Models\Pengurus;
use Illuminate\Database\Seeder;

class PengurusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PengurusController::getDefaultData() as $item) {
            Pengurus::updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );
        }
    }
}
