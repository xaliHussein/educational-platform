<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lessons;
use App\Models\Enrollments;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use App\Traits\Filter;
use App\Traits\Search;
use App\Traits\OrderBy;
use Illuminate\Support\Facades\Validator;

class LessonsController extends Controller
{
    use SendResponse, Pagination, Filter, OrderBy, UploadImage,Search;


    public function getLessons()
    {
        $lessons = Lessons::where("category_id", $_GET["category_id"]);
        if (isset($_GET["query"])) {
            $lessons = $this->search($lessons, 'lessons');
        }
        if (isset($_GET["order_by"])) {
            $lessons = $this->order_by($lessons, $_GET);
        }

        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;

        $res = $this->paging($lessons->orderBy("created_at", "ASC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على الدروس بنجاح', [], $res["model"], null, $res["count"]);
    }
}
