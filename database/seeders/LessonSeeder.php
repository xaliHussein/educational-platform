<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lessons;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lessons::create([
            "title" => "Test3",
            "content" => "lorem3",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "989f7622-76b1-4eb9-b43c-6dbbff75981d",
        ]);
        Lessons::create([
            "title" => "Test4",
            "content" => "lorem4",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "989f7622-76b1-4eb9-b43c-6dbbff75981d",
        ]);
        Lessons::create([
            "title" => "Test5",
            "content" => "lorem5",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "989f7622-76b1-4eb9-b43c-6dbbff75981d",
        ]);
        Lessons::create([
            "title" => "Test6",
            "content" => "lorem6",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "989f7622-76b1-4eb9-b43c-6dbbff75981d",
        ]);
        Lessons::create([
            "title" => "Test7",
            "content" => "lorem7",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "989f7622-76b1-4eb9-b43c-6dbbff75981d",
        ]);
    }
}
