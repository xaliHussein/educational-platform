<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Traits\Search;
use GuzzleHttp\Client;
use App\Traits\Pagination;
use App\Models\Enrollments;
use App\Traits\UploadImage;
use App\Models\PurchaseCode;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use App\Models\Course_Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Hawkiq\LaravelZaincash\Services\ZainCash;


class EnrollmentsController extends Controller
{
    use SendResponse;
    use UploadImage;
    use Pagination;
    use Search;

    public function random_code()
    {
        $code = substr(str_shuffle("0123456789ABCDEFGHIJKPQWRZX"), 0, 10);
        $get = Enrollments::where('order_id', $code)->first();
        if ($get) {
            return $this->random_code();
        } else {
            return $code;
        }
    }

    public function getEnrollments()
    {
        if(auth()->user()->user_type == 1) {
            $enrollments = Enrollments::where("teacher_id", auth()->user()->id)->where('status',1);
        } else {
            $enrollments = Enrollments::select("*")->where('status',1);
        }

        if (isset($_GET["query"])) {
            $enrollments = $this->search($enrollments, 'enrollments');
        }

        if (isset($_GET["filter"])) {
            $filter = json_decode($_GET["filter"]);
            $enrollments = $this->filter($enrollments, $_GET["filter"]);
        }

        if (isset($_GET["order_by"])) {
            $enrollments = $this->order_by($enrollments, $_GET);
        }

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($enrollments->orderBy("created_at", "DESC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم الحصول على الاشتراكات بنجاح', [], $res["model"], null, $res["count"]);
    }



    public function checkEnrollmentsLessons(Request $request)
    {
        $request = $request->json()->all();

        $course_categories = Course_Category::find($request['category_id']);
        if ($course_categories->course_type == 1) {
            $lessons_enrollments = Enrollments::where("category_id", $request['category_id'])
                ->where("user_id", auth()->user()->id)->where('status',1)->get();
            return $this->send_response(200, 'تمت عملية التحقق بنجاح', [], $lessons_enrollments);
        } else {
            $course_categories_free = "free";
            return $this->send_response(200, 'تمت عملية التحقق بنجاح', [], $course_categories_free);
        }
    }

    public function buyCourseCode(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'category_id' => 'required|exists:course__categories,id',
            'code' => 'required|exists:purchase_codes,code',

        ], [
            'code.required' => 'يرجى ادخال كود الشراء',
            'code.exists' => 'ادخلت كود شراء غير موجود',
            'category_id.required' => 'يرجى ادخال اسم الكورس',
            'category_id.exists' => 'ادخلت كورس غير موجود',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $purchase_codes = PurchaseCode::where('code', $request['code'])->first();
        $course = Course_Category::find($request['category_id']);

        if($purchase_codes->status == 1) {
            return $this->send_response(400, "ادخلت رمز شراء مستخدم", [], []);
        }
        if($purchase_codes->category_id != $request['category_id']) {
            return $this->send_response(400, "ادخلت رمز شراء غير صحيح", [], []);
        }

        $order_id = $this->random_code();

        $data = [];
        $data['payment_type'] = 0;
        $data['order_id'] = $order_id;
        $data['category_id'] = $request['category_id'];
        $data['user_id'] = auth()->user()->id;
        $data['teacher_id'] = $course->user_id;
        $data['status'] = 1;
        $data['subscription_time'] = Carbon::now()->format('Y-m-d');

        if($course->offer != null &&  Carbon::now()->format('Y-m-d') <=  $course->offer_expired) {
            $data['offer'] = $course->offer;
            $data['price'] = ($course->price - ($course->price / 100) * $course->offer) ;
        }else{
            $data['price'] = $course->price;
        }

        $enrollment = Enrollments::create($data);
        $purchase_codes->update([
            'status' => 1
        ]);
        $enrollment = Enrollments::where('id', $enrollment->id)->first();

        $pdf = PDF::loadView('Invoice', compact('enrollment'));
        $fileName = 'invoice_' . $order_id . '.pdf';
        $filePath = public_path('/Invoice_pdf/' . $fileName);
        $pdf->save($filePath);

        $enrollment->update([
            'invoice' => '/Invoice_pdf/' . $fileName
        ]);

        return $this->send_response(200, 'تمت عملية الشراء بنجاح', [], $enrollment->invoice);
    }
    public function makePayment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'category_id' => 'required|exists:course__categories,id',
        ], [
            'category_id.required' => 'يرجى ادخال اسم الكورس',
            'category_id.exists' => 'ادخلت كورس غير موجود',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $course = Course_Category::find($request['category_id']);
        $order_id = $this->random_code();


        $data = [];

        $data['payment_type'] = 1;
        $data['order_id'] = $order_id;
        $data['category_id'] = $request['category_id'];
        $data['user_id'] = auth()->user()->id;
        $data['teacher_id'] = $course->user_id;
        $data['status'] = 0;
        $data['subscription_time'] = Carbon::now()->format('Y-m-d');
        if($course->offer != null &&  Carbon::now()->format('Y-m-d') <=  $course->offer_expired) {
            $data['offer'] = $course->offer;
            $data['price'] = ($course->price - ($course->price / 100) * $course->offer) ;
        }else{
            $data['price'] = $course->price;
        }

        $enrollment = Enrollments::create($data);

        $enrollment = Enrollments::where('id', $enrollment->id)->first();
        $pdf = PDF::loadView('Invoice', compact('enrollment'));
        $fileName = 'invoice_' . $order_id . '.pdf';
        $filePath = public_path('/Invoice_pdf/' . $fileName);
        $pdf->save($filePath);

        $enrollment->update([
            'invoice' => '/Invoice_pdf/' . $fileName
        ]);

        $zaincash = new ZainCash();
        $amount = $enrollment->price;
        $service_type = "Course";
        $order_id = $order_id;


        $payload =  $zaincash->request($amount, $service_type, $order_id);
        $targetUrl = $payload->getTargetUrl();

        return $this->send_response(200, 'تمت عملية تحويل الى زين كاش', [], $targetUrl);
    }


    public function pamentCheck()
    {

        $token = request()->input('token');
        if (isset($token)) {
            $zaincash = new ZainCash();
            $result = $zaincash->parse($token);
            // اذا كانت عملية الدفع ناجحه
            if ($result->status == 'success') { // success ||  failed  || pending
                // حذف عبارة لارفل من اوردر اي دي
                $prefix = "laravel_hawkiq_";
                $orderid = $result->orderid;
                if (strpos($orderid, $prefix) === 0) {
                    $orderid = substr($orderid, strlen($prefix));
                }
                $enrollments = Enrollments::where('order_id', $orderid)->first();
                $data_vue = [
                    'status' => $result->status,
                    'url_invoice' => $enrollments->invoice,
                ];

                $data = [
                    'status' => 1,
                ];
                $enrollments->update($data);
                return redirect()->away('http://localhost:8080/enrollment?data=' . json_encode($data_vue));

            } elseif($result->status == 'failed') {
                $data = [
                    'status' => $result->status,
                ];
                return redirect()->away('http://localhost:8080/enrollment?data=' . json_encode($data));
            }
        }

        return $this->send_response(401, 'غير مصرح لك بدخول', [], []);
    }
}
