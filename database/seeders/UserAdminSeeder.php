<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'      => 'Administrador',
            'email'     => 'geradorpeticao@gmail.com',
            'password'  => bcrypt('12345678'),
            'role'      => 'admin'
        ]);
    }
}
