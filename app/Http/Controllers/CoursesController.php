<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use Illuminate\Http\Request;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use App\Traits\Filter;
use App\Traits\OrderBy;
use App\Traits\Search;
use File;
use Illuminate\Support\Facades\Validator;

class CoursesController extends Controller
{
    use SendResponse, Pagination, Filter, OrderBy, UploadImage, Search;

    public function getCourses()
    {
        $courses = Courses::select("*");
        if (isset($_GET["query"])) {
            $courses = $this->search($courses, 'courses');
        }

        if (isset($_GET["filter"])) {
            $filter = json_decode($_GET["filter"]);
            $courses = $this->filter($courses, $_GET["filter"]);
        }

        if (isset($_GET["order_by"])) {
            $courses = $this->order_by($courses, $_GET);
        }

        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;

        $res = $this->paging($courses->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على الكورسات بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addCourses(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'title' => 'required|unique:courses,title',
            'image' => 'required',
        ], [
            'title.required' => 'يرجى ادخال اسم الكورس',
            'title.unique' => 'اسم الكورس مستخدم',
            'image.required' => 'يرجى اضافة صورة الكورس',
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
    public function editCourses(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'title' => 'required|unique:courses,title,'. $request['id'],
        ], [
            'title.required' => 'يرجى ادخال اسم الكورس',
            'title.unique' => 'اسم الكورس مستخدم',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $courses = Courses::find($request['id']);
        if (array_key_exists('image', $request)) {
            $data['image'] = $this->uploadPicture($request['image'], '/images/courses/');
        }
        $data['title'] = $request['title'];

        $courses->update($data);
        return $this->send_response(200, 'تم تعديل الكورس بنجاح', [], Courses::find($courses->id));
    }

    public function deleteCourses(Request $request)
    {
        $courses = Courses::find($request["id"]);
        if (File::exists(public_path(), $courses->image)) {
            $image_path = public_path() . $courses->image;
            unlink($image_path);
        }
        $courses->delete();
        return $this->send_response(200, 'تم حذف الكورس بنجاح', [], []);
    }
}
