<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@unsil.ac.id'],
            [
                'name'     => 'Administrator UPZ',
                'password' => Hash::make('password'),
                'role'     => 'administrator',
            ]
        );

        User::updateOrCreate(
            ['email' => 'bendahara@unsil.ac.id'],
            [
                'name'     => 'Bendahara UPZ Unsil',
                'password' => Hash::make('password'),
                'role'     => 'administrator',
            ]
        );
    }
}

