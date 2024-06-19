<?php

namespace App\Http\Controllers;

use App\Traits\Pagination;
use App\Models\Enrollments;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use App\Models\Course_Category;
use Illuminate\Support\Facades\Validator;
use Hawkiq\LaravelZaincash\Services\ZainCash;


class EnrollmentsController extends Controller
{
    use SendResponse, UploadImage, Pagination;

    public function checkEnrollmentsLessons(Request $request)
    {
        $request = $request->json()->all();

        $course_categories = Course_Category::find($request['category_id']);
        if ($course_categories->course_type == 1) {
            $lessons_enrollments = Enrollments::where("category_id", $request['category_id'])
                ->where("user_id", auth()->user()->id)->get();
            return $this->send_response(200, 'تمت عملية التحقق بنجاح', [], $lessons_enrollments);
        } else {
            $course_categories_free = "free";
            return $this->send_response(200, 'تمت عملية التحقق بنجاح', [], $course_categories_free);
        }
    }

    public function createEnrollment()
    {
        $zaincash = new ZainCash();
        $amount = 1000;
        $service_type = "Shirt";
        $order_id = "20222009";
        $payload =  $zaincash->request($amount, $service_type, $order_id);
        return $payload;
    }

    public function redirect()
    {
        // return redirect('http://localhost:8080/available-courses');
        $token = \Request::input('token');
        if (isset($token)) {
            $zaincash = new ZainCash();
            $result = $zaincash->parse($token);
            if ($result->status == 'success'){ // success ||  failed  || pending
                return 'Thanks for Buying';
                // We can do what ever you like , insert transaction into database, send email etc..
            }
        }
    }

}
