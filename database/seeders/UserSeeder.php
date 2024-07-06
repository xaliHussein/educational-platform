<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "email" => "ali_hussein@gmail.com",
            "name" => "ali hussein",
            "password" => bcrypt("fffjjjqq"),
            "user_type" => 0,
            "random_code" => 0,
            "account_status" => 1,
        ]);

    }
}
