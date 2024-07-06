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
            "title" => "Test1",
            "content" => "lorem1",
            "video" => "/videos/courses/BattlefieldV.mp4",
            "category_id" => "d0749f4f-2572-443d-a817-0c730083b17b",
        ]);
        Lessons::create([
            "title" => "Test2",
            "content" => "lorem2",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "d0749f4f-2572-443d-a817-0c730083b17b",
        ]);
        Lessons::create([
            "title" => "Test3",
            "content" => "lorem3",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "d0749f4f-2572-443d-a817-0c730083b17b",
        ]);
        Lessons::create([
            "title" => "Test4",
            "content" => "lorem4",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "d0749f4f-2572-443d-a817-0c730083b17b",
        ]);
        Lessons::create([
            "title" => "Test5",
            "content" => "lorem5",
            "video" => "/videos/courses/ui-attmaq.mp4",
            "category_id" => "d0749f4f-2572-443d-a817-0c730083b17b",
        ]);
    }
}
