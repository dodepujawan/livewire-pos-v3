<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user Dwebpro
        $user = User::firstOrCreate(
            ['email' => 'dwebpro@gmail.com'],
            [
                'name' => 'Dwebpro',
                'password' => Hash::make('admin123'),
            ]
        );

        // Assign ke role Super Admin
        Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $user->syncRoles(['Super Admin']);
    }
}
