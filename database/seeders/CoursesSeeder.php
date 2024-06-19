<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Courses;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Courses::create([
            'image'=>'/images/courses/Capture2.PNG',
            'title'=>'برمجة تطبيقات الويب'
        ]);
        Courses::create([
            'image'=>'/images/courses/Capture3.PNG',
            'title'=>'احتراف التسويق الرقمي'
        ]);
        Courses::create([
            'image'=>'/images/courses/Capture.PNG',
            'title'=>'برمجة تطبيقات الموبايل'
        ]);
        Courses::create([
            'image'=>'/images/courses/Capture1.PNG',
            'title'=>'احتراف قواعد البيانات'
        ]);
    }
}
