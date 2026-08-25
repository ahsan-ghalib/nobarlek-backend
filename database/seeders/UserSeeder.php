<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@upliga.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'avatar' => 'https://via.placeholder.com/150',
            ]
        );

        $this->call(PermissionSeeder::class);
    }
}
