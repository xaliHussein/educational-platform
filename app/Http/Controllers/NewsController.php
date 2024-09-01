<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use App\Models\Images;
use App\Traits\Filter;
use App\Traits\Search;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use Illuminate\Support\Str;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    use SendResponse;
    use Pagination;
    use Filter;
    use OrderBy;
    use UploadImage;
    use Search;

    public function random_code()
    {
        $code = substr(str_shuffle("0123456789"), 0, 6);
        return $code;
    }

    public function getNews()
    {
        $news = News::select("*");
        if (isset($_GET["query"])) {
            $news = $this->search($news, 'news');
        }
        if (isset($_GET["order_by"])) {
            $news = $this->order_by($news, $_GET);
        }

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($news->orderBy("created_at", "DESC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم احضار جميع الاخبار بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addNews(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'text' => 'required|string|max:1250',
        ], [
            'text.required' => 'يرجى ادخال  النص',
            'text.max' => 'الحد الاقصى لعدد الاحرف هوه 1250 حرف',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data['text'] = $request['text'];
        $data['user_id'] = auth()->user()->id;

        if (isset($request['file'])) {
            $pdfFile = $request['file'];
            $fileName = 'file_' . $this->random_code() . '.pdf';
            $directory = 'files/';
            $filePath = public_path($directory . $fileName);

            if (!File::exists(public_path($directory))) {
                File::makeDirectory(public_path($directory), 0755, true);
            }
            $pdfFile->move(public_path($directory), $fileName);
            $data['file'] = $directory . $fileName;
        }

        $news = News::create($data);

        if (isset($request['images'])) {
            foreach ($request['images'] as $image) {
                $news->images()->create([
                    "image_path" => $this->uploadPicture($image, '/images/news'),
                ]);
            }
        }

        return $this->send_response("200", 'تم انشاء خبر جديد  بنجاح', [], News::find($news->id));
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

    public function deleteNews(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:news,id',
        ], [
            'id.required' => 'لم يتم اضافة معرف',
            'id.exists' => 'هذا الخبر غير موجود',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $news = News::find($request["id"]);
        $images = Images::where("imageable_id", $news->id)->get();
        // حذف جميع الصور لزبون من السيرفر و من ملفات المشروع
        if(isset($images) != null) {
            foreach($images as $image) {
                $image_path = public_path($image->image_path);
                if (File::exists($image_path)) {
                    unlink($image_path);
                }
                $image->delete();
            }
        }
        $news->delete();
        return $this->send_response(200, 'تم حذف الخبر بنجاح', [], []);
    }
}
