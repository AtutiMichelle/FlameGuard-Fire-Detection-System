<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       User::updateOrCreate(
            ['email' => 'michelle.atuti@strathmore.edu'],
            [
                'name' => 'Flameguard Admin',
                'password' => Hash::make('9576argentum'), // change after install
                'role' => 'admin',
            ]
        );
    }
}
