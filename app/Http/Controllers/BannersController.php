<?php

namespace App\Http\Controllers;

use PDF;
use File;
use App\Traits\Filter;
use App\Models\Banners;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Models\Enrollments;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        return $this->send_response(200, 'تم احضار جميع الاعلانات', [], $res["model"], null, $res["count"]);
    }

    public function addBanners(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'value' => 'required',
            'image' => 'required',
        ], [
            'value.required' => 'يرجى ادخال  قيمه',
            'image.required' => 'لم تضف صورة  الاعلان',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data['value'] = $request['value'];
        $data['image'] = $this->uploadPicture($request['image'], '/images/banners/');
        $banners = Banners::create($data);

        return $this->send_response("200", 'تم عملية اضافة اعلان بنجاح', [], Banners::find($banners->id));
    }

    public function editBanners(Request $request)
    {
        $request = $request->json()->all();
        $banners = Banners::find($request['id']);

        $data = [];
        if (array_key_exists('image', $request)) {
            $data['image'] = $this->uploadPicture($request['image'], '/images/banners/');
        }
        $data["value"] = $request['value'];
        $banners->update($data);

        return $this->send_response(200, 'تم تعديل الاعلان بنجاح', [], Banners::find($banners->id));
    }

    public function deleteBanners(Request $request)
    {
        $banners = Banners::find($request["id"]);
        if (File::exists(public_path(), $banners->image)) {
            $image_path = public_path() . $banners->image;
            unlink($image_path);
        }
        $banners->delete();
        return $this->send_response(200, 'تم حذف الاعلان بنجاح', [], []);
    }


}
