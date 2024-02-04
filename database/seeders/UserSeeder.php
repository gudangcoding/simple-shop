<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::factory(9)->create(); // 9 user dengan level 'user'
        \App\Models\User::factory()->create(['level' => 'admin']); // 1 admin
    }
}
