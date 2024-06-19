<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use App\Mail\EducationalMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    use SendResponse, UploadImage, Pagination;

    public function random_code()
    {
        $code = substr(str_shuffle("0123456789"), 0, 6);
        return $code;
    }

    public function login(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'email' => 'required',
            'password' => 'required'
        ], [
            'email.required' => 'يرجى ادخال البريد الالكتروني ',
            'password.required' => 'يرجى ادخال كلمة المرور ',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        if (auth()->attempt(array('email' => $request['email'], 'password' => $request['password']))) {
            $user = auth()->user();
            if ($user->account_status == 1) {
                $token = $user->createToken('educational_platform')->accessToken;
                return $this->send_response(200, 'تم تسجيل الدخول بنجاح', [], $user, $token);
            } else {
                return $this->send_response(400, 'يرجى تاكيد عنوان بريدك الالكتروني', null, null, null);
            }
        } else {
            return $this->send_response(400, 'هناك مشكلة تحقق من تطابق المدخلات', null, null, null);
        }
    }

    public function register(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required'
        ], [
            'name.required' => 'حقل الاسم مطلوب',
            'email.required' => 'يرجى ادخال البريد الالكتروني',
            'email.unique' => 'البريد الالكتروني الذي قمت بأدخاله تم استخدامه سابقاً',
            'password.required' => 'حقل كلمة المرور مطلوب',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $data = [];
        $random_code = $this->random_code();
        $data = [
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            'account_status' => 0,
            'random_code' => $random_code,
            'user_type' => 2,
        ];
        if (array_key_exists('image', $request)) {
            $data['image'] = $this->uploadPicture($request['image'], '/images/user/');
        }
        $user = User::create($data);
        $mail_data = [
            "title" => "مرحبا " . $request['name'],
            "body" => " رمز التحقق الخاص بك :" . $random_code,
        ];
        Mail::to($request['email'])->send(new EducationalMail($mail_data));
        return $this->send_response(200, 'يرجى التحقق من البريد الالكتروني', [], User::find($user->id));
    }

    public function getUser()
    {
        $user = User::find(auth()->user()->id);
        return $this->send_response(200, 'تم احضار معلومات المستخدم', [], $user);
    }


    public function sendCodeAgain(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required',
        ]);
        $user = User::find($request["id"]);
        $data = [];
        $random_code = $this->random_code();
        $data = [
            'random_code' => $random_code,
        ];
        $user->update($data);
        $mail_data = [
            "title" => "مرحبا " . $user->email,
            "body" => " رمز التحقق الخاص بك :" . $random_code,
        ];
        Mail::to($user->email)->send(new EducationalMail($mail_data));
        return $this->send_response(200, 'تم اعادة ارسال رمز التحقق', [], null, null);
    }
    public function emailVerificationRegister(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required',
            'random_code' => 'required',
        ]);
        $user = User::find($request["id"]);
        if ($request["random_code"] == $user->random_code) {
            $data = [];
            $data["account_status"] = 1;
            $user->update($data);
            return $this->send_response(200, 'تم التحقق من حسابك يمكنك الان تسجيل الدخول', null, null);
        } else {
            return $this->send_response(400, 'ادخلت رمز تحقق خاطى', null, null, null);
        }
    }

    public function updateUser(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "id" => "required",
            "name" => "required|string|max:255|min:3",
            "password" => "required|string|max:255|min:8",
        ], [
            'name.required' => 'حقل الاسم مطلوب',
            'password.required' => 'حقل كلمة المرور مطلوب',

        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }

        $user = User::find($request['id']);
        if (Hash::check($request['password'], $user->password)) {
            $user->update([
                "name" => $request['name'],
            ]);
        } else {
            return $this->send_response(400, 'ادخلت كلمة مرور غير صحيحه', [], []);
        }
        return $this->send_response(200, 'تم تحديث المعلومات المستخدم بنجاح', [], $user);
    }

    public function updatePasswordUser(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "id" => "required",
            "password_old" => "required|string|max:255|min:8",
            "password_new" => "required|string|max:255|min:8",
        ], [
            'password_old.required' => 'حقل كلمة المرور القديمه مطلوب',
            'password_new.required' => 'حقل كلمة المرور الجديده مطلوب',

        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::find($request['id']);
        if (Hash::check($request['password_old'], $user->password)) {
            $user->update([
                'password' => bcrypt($request['password_new'])
            ]);
        } else {
            return $this->send_response(400, 'كلمة المرور السابقة غير صحيحه', [], []);
        }
        return $this->send_response(200, 'تم تحديث المعلومات الطالب بنجاح', 'تم تحديث كلمة المرور بنجاح');
    }

    public function updateImageUser(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:users,id',
            'image' => 'required',
        ], [
            'image.required' => 'لم ترفق اي صوره',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل عملية ', $validator->errors(), []);
        }
        $user = User::find($request['id']);
        $user->update([
            'image' => $this->uploadPicture($request['image'], '/images/user')
        ]);
        return $this->send_response(200, 'تم تغير الصورة بنجاح', [], User::find($user->id));
    }

    public function updateEmailUser(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "id" => "required",
            "password" => "required|string|max:255|min:8",
            "email" => "required|string|unique:users,email," . $request['id'],
        ], [
            'email.required' => 'حقل البريد الالكتروني مطلوب',
            'email.unique' => ' البريد الالكتروني الذي قمت بأدخاله مستخدم سابقا',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::find($request['id']);
        if (Hash::check($request['password'], $user->password)) {

            $data = [];
            $random_code = $this->random_code();
            $data = [
                'random_code' => $random_code,
            ];
            $user->update($data);
            $mail_data = [
                "title" => "مرحبا " . $user->email,
                "body" => " رمز التحقق الخاص بك :" . $random_code,
            ];
            Mail::to($request['email'])->send(new EducationalMail($mail_data));

            return $this->send_response(200, 'تم ارسال رمز التحقق', [], User::find($user->id));
        } else {
            return $this->send_response(400, 'ادخلت كلمة مرور غير صحيحه', [], []);
        }
    }
    public function emailVerificationUpdate(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required',
            'random_code' => 'required',
            "email" => "required|string|unique:users,email," . $request['email'],
        ], [
            'random_code.required' => 'رمز التاكيد مطلوب',
            'email.required' => 'حقل البريد الالكتروني مطلوب',
            'email.unique' => ' البريد الالكتروني الذي قمت بأدخاله مستخدم سابقا',
        ]);
        $user = User::find($request["id"]);
        if ($request["random_code"] == $user->random_code) {
            $data = [
                'email' => $request["email"],
            ];
            $user->update($data);
            return $this->send_response(200, 'تم تغير بريد الالكتروني بنجاح', null, null);
        } else {
            return $this->send_response(400, 'ادخلت رمز تحقق غير صحيح', null, null, null);
        }
    }

    // public function addUser(Request $request)
    // {
    //     $request = $request->json()->all();
    //     $validator = Validator::make($request, [
    //         'phone_number' => 'required|unique:users,phone_number',
    //         'user_name' => 'required|unique:users,user_name',
    //         'password' => 'required',
    //         'user_type' => 'required',
    //     ], [
    //         'phone_number.required' => 'يرجى ادخال رقم الهاتف',
    //         'user_name.required' => 'يرجى ادخال اسم المستخدم ',
    //         'phone_number.unique' => 'رقم الهاتف الذي قمت بأدخاله تم استخدامه سابقاً',
    //         'user_name.unique' => 'اسم المستخدم الذي قمت بأدخاله تم استخدامه سابقاً',
    //         'password.required' => 'يرجى ادخال كلمة المرور ',
    //         'user_type.required' => 'يرجى ادخال  نوع المستخدم ',
    //     ]);
    //     if ($validator->fails()) {
    //         return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
    //     }
    //     $data = [];
    //     $data = [
    //         'user_name' => $request['user_name'],
    //         'phone_number' => $request['phone_number'],
    //         'password' => bcrypt($request['password']),
    //         'user_type' => $request['user_type'],
    //     ];
    //     if (array_key_exists('image', $request)) {
    //         $data['image'] = $this->uploadPicture($request['image'], '/images/user_images/');
    //     }
    //     $user = User::create($data);
    //     return $this->send_response(200, 'تم أضافة حساب بنجاح', [], User::find($user->id));
    // }
}
