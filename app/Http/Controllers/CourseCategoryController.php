<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Filter;
use App\Models\Course_Category;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use App\Traits\Search;
use File;

use Illuminate\Support\Facades\Validator;

class CourseCategoryController extends Controller
{
    use SendResponse;
    use Pagination;
    use Filter;
    use OrderBy;
    use UploadImage;
    use Search;

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

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($course_categories->orderBy("created_at", "DESC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على الكورسات بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function getCourseCategoryDashboard()
    {
        if(auth()->user()->user_type == 1) {
            $course_categories = Course_Category::where("user_id", auth()->user()->id);
        } else {
            $course_categories = Course_Category::select("*");
        }

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

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($course_categories->orderBy("created_at", "DESC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على الكورسات بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addCourseCategory(Request $request)
    {
        $request = $request->json()->all();
        $data = [];

        if ($request['course_type'] == 1) {
            $rules['price'] = 'required|numeric';
            $messages['price.required'] = 'يرجى ادخال سعر فئة الكورس';
            $data['price'] = $request['price'];
        }
        if (isset($request['offer'])) {

            $rules['offer_expired'] = 'required';
            $messages['offer_expired.required'] = 'يرجى ادخال  تاريخ انتهاء';
            $data['offer_expired'] = $request['offer_expired'];
            $data['offer'] = $request['offer'];
        }

        $rules = [
            'title' => 'required',
            'time_course' => 'required',
            'time_type' => 'required',
            'description' => 'nullable|string|max:510',
            'course_type' => 'required',
            'image' => 'required',
            'course_id' => 'required|exists:courses,id',
        ];
        $messages = [
            'title.required' => 'يرجى ادخال اسم فئة الكورس',
            'description.max' => ' وصف  فئة الكورس يجب ألا يتجاوز 510 حرفًا',
            'course_type.required' => 'يرجى ادخال نوع فئة الكورس',
            'time_course.required' => 'يرجى ادخال مدة فئة الكورس',
            'time_type.required' => 'يرجى ادخال نوع وقت فئة الكورس',
            'image.required' => 'يرجى اضافة صورة فئة الكورس',
            'course_id.required' => 'يرجى اختار الكورس',
        ];

        $validator = Validator::make($request, $rules, $messages);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $data['title'] = $request['title'];
        $data['user_id'] = auth()->user()->id;
        $data['description'] = $request['description'];
        $data['time_course'] = $request['time_course'];
        $data['course_type'] = $request['course_type'];
        $data['time_type'] = $request['time_type'];
        $data['course_id'] = $request['course_id'];
        $data['image'] = $this->uploadPicture($request['image'], '/images/course_category/');
        $courses = Course_Category::create($data);

        return $this->send_response("200", 'تم عملية اضافة الكورس بنجاح', [], Course_Category::find($courses->id));
    }
    public function editCoursesCategory(Request $request)
    {
        $request = $request->json()->all();
        $data = [];
        $course_category = Course_Category::find($request["id"]);

        if ($request['course_type'] == 1) {
            $rules['price'] = 'required|numeric';
            $messages['price.required'] = 'يرجى ادخال سعر فئة الكورس';
            $data['price'] = $request['price'];
        }
        if (isset($request['offer'])) {
            $rules['offer_expired'] = 'required';
            $messages['offer_expired.required'] = 'يرجى ادخال  تاريخ انتهاء';
            $data['offer_expired'] = $request['offer_expired'];
            $data['offer'] = $request['offer'];
        }

        $rules = [
            'id' => 'required|exists:course__categories,id',
            'title' => 'required',
            'time_course' => 'required',
            'time_type' => 'required',
            'course_type' => 'required',
            'description' => 'nullable|string|max:510',
            'course_id' => 'required|exists:courses,id',
        ];
        $messages = [
            'id.exists' => 'فئة الكورس غير متوفره',
            'description.max' => ' وصف  فئة الكورس يجب ألا يتجاوز 510 حرفًا',
            'title.required' => 'يرجى ادخال اسم فئة الكورس',
            'course_type.required' => 'يرجى ادخال نوع فئة الكورس',
            'time_type.required' => 'يرجى ادخال نوع وقت فئة الكورس',
            'time_course.required' => 'يرجى ادخال مدة فئة الكورس',
            'course_id.required' => 'يرجى اختار الكورس',
        ];

        $validator = Validator::make($request, $rules, $messages);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        if (array_key_exists('image', $request)) {
            // حذف الصورة القديمه
            if (File::exists(public_path(), $course_category->image)) {
                $image_path = public_path() . $course_category->image;
                unlink($image_path);
            }
            // تعديل الصورة
            $data['image'] = $this->uploadPicture($request['image'], '/images/course_category/');
        }

        $data['title'] = $request['title'];
        $data['time_course'] = $request['time_course'];
        $data['course_type'] = $request['course_type'];
        $data['time_type'] = $request['time_type'];
        $data['description'] = $request['description'];
        $data['course_id'] = $request['course_id'];

        $course_category->update($data);

        return $this->send_response("200", 'تم عملية تعديل فئة الكورس بنجاح', [], Course_Category::find($course_category->id));
    }

    public function deleteCoursesCategory(Request $request)
    {
        $course_category = Course_Category::find($request["id"]);
        if (File::exists(public_path(), $course_category->image)) {
            $image_path = public_path() . $course_category->image;
            unlink($image_path);
        }
        $course_category->delete();
        return $this->send_response(200, 'تم حذف الكورس بنجاح', [], []);
    }
}
