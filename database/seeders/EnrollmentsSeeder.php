<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Enrollments;

class EnrollmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Enrollments::create([
            "category_id" => "989f7622-76b1-4eb9-b43c-6dbbff75981d",
            "user_id" => "15d4e2bc-edd7-4ceb-a04f-e1b384af991b",
            "access_type" => 1,
        ]);
    }
}
