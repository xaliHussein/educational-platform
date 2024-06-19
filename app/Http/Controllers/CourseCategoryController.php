<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Filter;
use App\Models\Course_Category;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;


use Illuminate\Support\Facades\Validator;

class CourseCategoryController extends Controller
{
    use SendResponse, Pagination, Filter, OrderBy, UploadImage;

    public function getCourseCategory()
    {
        $course_categories = Course_Category::select("*");
        if (isset($_GET["query"])) {
            $course_categories = $this->search($course_categories, 'course__categories');
        }

        if (isset($_GET["filter"])) {
            $filter = json_decode($_GET["filter"]);
            $course_categories = $this->filter($course_categories, $_GET["filter"]);
        }
        if (isset($_GET["filter_paid_course"])) {
            $filter = json_decode($_GET["filter_paid_course"]);
            $course_categories = $this->filter($course_categories, $_GET["filter_paid_course"]);
        }

        if (isset($_GET["order_by"])) {
            $course_categories = $this->order_by($course_categories, $_GET);
        }

        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;

        $res = $this->paging($course_categories->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على الكورسات بنجاح', [], $res["model"], null, $res["count"]);
    }
}
