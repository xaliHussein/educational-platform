<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course_Category;

class Course_CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Course_Category::create([
        //     'image'=>'/images/courses/MongoDB.png',
        //     'title'=>'مقدمة في قواعد بيانات MongoDB',
        //     'description'=>'Mongo DB هي إحدى قواعد بيانات NoSql وهي الجيل التالي من أنظمة إدارة قواعد البيانات الأكثر ملاءمة لتطبيقات Web 2 مثل تطبيقات التواصل الاجتماعي.
        //     الشركات مثل ebay و Adobe و Google و GAP و PayPal وغيرها تستخدم Mongo DB لأنها مرنة بما يكفي لتناسب أي صناعة.
        //     لا تقتصر استخدامات Mongo DB على تطوير الحزم المتوسطة فقط، لأنها تحتوي على محركات للعديد من التقنيات الرائدة مثل Net و PHP و Ruby وغيرها.
        //     يعرفك هذا الكورس قواعد بيانات NoSql وقدرات Mongo DB كونها واحدة من قواعد بيانات NoSql DBMS لإنشاء قاعدة بيانات خاصة بك، مرنة وخفيفة وقابلة للتكيف مع السحابة.',
        //     'course_id'=>'5d95387a-48fc-42a4-a109-27e600ab3a4a',
        //     'user_id'=>'15d4e2bc-edd7-4ceb-a04f-e1b384af991b',
        //     'price'=>null,
        //     'course_type'=> 0,
        // ]);
        Course_Category::create([
            'image'=>'/images/courses/mysql.jpg',
            'title'=>'مقدمة في قواعد بيانات Mysql',
            'description'=>'MySQL هو نظام مفتوح المصدر لإدارة قواعد البيانات الترابطية ويستند إلى SQL. وقد تم تصميمه وتحسينه لتطبيقات الويب ويمكنه العمل على أي نظام أساسي. وعندما نشأت متطلبات جديدة ومختلفة مع ظهور الإنترنت، أصبح MySQL النظام الأساسي المفضل لمطوري الويب والتطبيقات المستندة إلى الويب.',
            'course_id'=>'5d95387a-48fc-42a4-a109-27e600ab3a4a',
            'user_id'=>'15d4e2bc-edd7-4ceb-a04f-e1b384af991b',
            'price'=>100000,
            'time_course'=>'30 ساعات',
            'course_type'=> 1,
        ]);
        Course_Category::create([
            'image'=>'/images/courses/mariadb.png',
            'title'=>'مقدمة في قواعد بيانات MariaDB',
            'description'=>'هي قاعدة بيانات تعتمد على تطوير المجتمع، اشتقت وتفرعت عن قاعدة بيانات ماي إس كيو إل. والدافع لهذا التفرع هو المحافظة عليها حرة وتحت ترخيص رخصة جنو العمومية في مواجهة الشكوك التي تدور حول ترخيصها عند مالكها الجديد شركة أوراكل. ويطلب من المساهمين مشاركة حقوقهم مع شركة Monty Program Ab.',
            'course_id'=>'5d95387a-48fc-42a4-a109-27e600ab3a4a',
            'user_id'=>'15d4e2bc-edd7-4ceb-a04f-e1b384af991b',
            'price'=>null,
            'time_course'=>'10 ساعات',
            'course_type'=> 0,
        ]);
    }
}
