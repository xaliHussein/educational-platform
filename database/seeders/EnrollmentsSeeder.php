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
            "category_id" => "d0749f4f-2572-443d-a817-0c730083b17b",
            "user_id" => "62362711-b55d-4b76-927c-c516f649a801",
            "payment_type" => 0,
            "price" => 50000,
        ]);
        Enrollments::create([
            "category_id" => "d0749f4f-2572-443d-a817-0c730083b17b",
            "user_id" => "62362711-b55d-4b76-927c-c516f649a801",
            "payment_type" => 0,
            "price" => 60000,
        ]);
    }
}
