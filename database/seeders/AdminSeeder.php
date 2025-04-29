<?php

namespace Database\Seeders;

use App\Models\admin;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        admin::create([
            'name' => 'admin khaled',
            'email' => 'adminkhaled@gmail.com',
            'password' => bcrypt('khaled123'),
            'role' => 'super_admin',

        ]);
    }
}
