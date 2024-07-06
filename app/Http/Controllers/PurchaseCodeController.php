<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Filter;
use App\Models\PurchaseCode;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\SendResponse;
use Illuminate\Support\Facades\Validator;


class PurchaseCodeController extends Controller
{
    use SendResponse, Pagination, Filter, OrderBy;


    public function random_code()
    {
        $code = substr(str_shuffle("0123456789ABCDEFGHIJKLMPQWRZX"), 0, 8);
        $get = PurchaseCode::where('code', $code)->first();
        if ($get) {
            return $this->random_code();
        } else {
            return $code;
        }
    }

    public function getPurchaseCode()
    {
        if(auth()->user()->user_type == 1) {
            $purchase_code = PurchaseCode::where("teacher_id", auth()->user()->id);
        } else {
            $purchase_code = PurchaseCode::select("*");
        }

        if (isset($_GET)) {
            $this->order_by($purchase_code, $_GET);
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($purchase_code->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200, 'تم الحصول اكواد الشراء بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addPurchaseCode(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'category_id' => 'required|exists:course__categories,id',
        ], [
            'category_id.required' => 'لم تدخل اسم الكورس',
            'category_id.exists' => 'الكورس غير متوفر',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data['teacher_id'] = auth()->user()->id;
        $data['category_id'] = $request['category_id'];
        $data['code'] = $this->random_code();
        $purchase_code = PurchaseCode::create($data);

        return $this->send_response("200", 'تم اضافة كود الشراء بنجاح', [], PurchaseCode::find($purchase_code->id));
    }

}
