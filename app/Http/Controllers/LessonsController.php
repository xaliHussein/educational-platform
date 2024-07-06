<?php

namespace App\Http\Controllers;

use File;
use App\Traits\Filter;
use App\Traits\Search;
use App\Models\Lessons;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Models\Enrollments;
use App\Traits\UploadImage;
use Illuminate\Support\Str;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use App\Models\Course_Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LessonsController extends Controller
{
    use SendResponse;
    use Pagination;
    use Filter;
    use OrderBy;
    use UploadImage;
    use Search;


    public function getLessons()
    {
        $lessons = Lessons::where("category_id", $_GET["category_id"]);
        if (isset($_GET["query"])) {
            $lessons = $this->search($lessons, 'lessons');
        }
        if (isset($_GET["order_by"])) {
            $lessons = $this->order_by($lessons, $_GET);
        }

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($lessons->orderBy("created_at", "ASC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على الدروس بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function getLessonsDashboard()
    {

        if(auth()->user()->user_type == 1) {
            $lessons = Lessons::where("user_id", auth()->user()->id);
        } else {
            $lessons = Lessons::select("*");
        }

        if (isset($_GET["query"])) {
            $lessons = $this->search($lessons, 'lessons');
        }
        if (isset($_GET["order_by"])) {
            $lessons = $this->order_by($lessons, $_GET);
        }

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($lessons->orderBy("created_at", "ASC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على الدروس بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function getCoursesCategoryLessons()
    {
        $lessons = Course_Category::where("user_id", auth()->user()->id);
        if (isset($_GET["order_by"])) {
            $lessons = $this->order_by($lessons, $_GET);
        }

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($lessons->orderBy("created_at", "ASC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على فئات الكورس بنجاح', [], $res["model"], null, $res["count"]);
    }
    public function uploadVedioLessons(Request $request)
    {
        $path = '/videos/lessons/';
        $video = $request->file('file');
        $mime_type = $video->getClientMimeType();
        $mime_to_ext = [
            'video/mp4' => 'mp4',
            'video/x-matroska' => 'mkv',
            'video/webm' => 'webm',
        ];
        if (!array_key_exists($mime_type, $mime_to_ext)) {
            return $this->send_response(400, 'تنسيق الفيديو غير مدعوم', ["تنسيق الفيديو غير مدعوم"], []);
        }

        $extension = $mime_to_ext[$mime_type];
        $filename = time() . Str::random(2) . '.' . $extension;

        if (!file_exists(public_path() . $path)) {
            File::makeDirectory(public_path() . $path, 0755, true);
        }
        $video->move(public_path() . $path, $filename);
        $path_viedo = $path . $filename;

        return $this->send_response(200, 'تم تحميل الفيديو بنجاح', [], $path_viedo);

    }
    public function deleteVedioLessons(Request $request)
    {
        $request = $request->json()->all();

        if (File::exists(public_path(), $request['vedio'])) {
            $video_path = public_path() . $request['vedio'];
            unlink($video_path);

            return $this->send_response(200, 'تم حذف الفيديو بنجاح', [], []);
        } else {
            return $this->send_response(200, 'الفيديو غير متوفر', ['الفيديو غير متوفر'], []);
        }
    }

    public function addLessons(Request $request)
    {
        $request = $request->json()->all();
        $data = [];

        $rules = [
            'title' => 'required|string|max:100',
            'content' => 'nullable|string|max:510',
            'upload_type' => 'required',
            'category_id' => 'required|exists:course__categories,id',
        ];
        $messages = [
            'title.required' => 'يرجى ادخال اسم فئة الكورس',
            'upload_type.required' => 'يرجى ادخال  نوع رفع الفيديو',
            'content.max' => ' وصف الدرس يجب ألا يتجاوز 510 حرفًا',
            'category_id.required' => 'يرجى ادخال  اسم فئة الكورس',
            'category_id.exists' => 'اسم فئة الكورس غير موجود',
        ];
        if ($request['upload_type'] == 1) {
            if(isset($request['image'])) {
                $data['image'] = $this->uploadPicture($request['image'], '/images/lessons/');
            }
            $rules['video'] = 'required';
            $messages['video.required'] = 'يرجى اضافة فيديو الدرس';
            $data['video'] = $request['video'];
        } else {
            $rules['video_url'] = 'required';
            $messages['video_url.required'] = 'يرجى ادخال رابط فيديو الدرس';
            $data['video_url'] = $request['video_url'];
        }


        $validator = Validator::make($request, $rules, $messages);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $data['title'] = $request['title'];
        $data['user_id'] = auth()->user()->id;
        $data['content'] = $request['content'];
        $data['category_id'] = $request['category_id'];
        $data['upload_type'] = $request['upload_type'];
        $lessons = Lessons::create($data);

        return $this->send_response("200", 'تم عملية اضافة الكورس بنجاح', [], Lessons::find($lessons->id));
    }


    public function deleteLessons(Request $request)
    {
        $lessons = Lessons::find($request["id"]);

        if ($lessons->image != null) {
            $image_path = public_path() . $lessons->image;
            unlink($image_path);
        }
        if ($lessons->video != null) {
            $video_path = public_path() . $lessons->video;
            unlink($video_path);
        }
        $lessons->delete();
        return $this->send_response(200, 'تم حذف الدرس بنجاح', [], []);
    }
}
