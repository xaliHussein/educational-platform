<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Filter;
use App\Models\Banners;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;

class BannersController extends Controller
{
    use SendResponse, Pagination, Filter, OrderBy, UploadImage;
    public function getBanners()
    {
        $faqs = Banners::select("*");
        if (isset($_GET)) {
            $this->order_by($faqs, $_GET);
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($faqs->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم جلب المنتجات في المخزن بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addBanners(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'title' => 'required',
        ], [
            'title.required' => 'يرجى ادخال اسم الكورس',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data['title'] = $request['title'];
        $data['image'] = $this->uploadPicture($request['image'], '/images/courses/');
        $courses = Courses::create($data);

        return $this->send_response("200", 'تم عملية اضافة الكورس بنجاح', [], Courses::find($courses->id));
    }
}
