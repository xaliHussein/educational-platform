<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use App\Traits\Search;
use Illuminate\Http\Request;
use App\Mail\EducationalMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    use SendResponse;
    use UploadImage;
    use Pagination;
    use Search;

    public function random_code()
    {
        $code = substr(str_shuffle("0123456789"), 0, 6);
        return $code;
    }
    public function create_login_code()
    {
        $code = substr(str_shuffle("0123456789ABCDEFGHIJKPQWRZX"), 6, 7);
        $get = User::where('login_code', $code)->first();
        if ($get) {
            return $this->create_login_code();
        } else {
            return $code;
        }
    }


    public function login(Request $request)
    {
        $request = $request->json()->all();

        if (isset($request['login_code'])) {

            $rules['login_code'] = 'required';
            $rules['mac_address'] = 'required|string';
            $messages['login_code.required'] = 'رمز تسجيل الدخول مطلوب';
            $messages['mac_address.required'] = 'معرف الجهاز مطلوب';

        } elseif (isset($request['email'])) {
            $rules['email'] = 'required';
            $rules['password'] = 'required|string|max:255|min:8';

            $messages['email.required'] = 'يرجى ادخال البريد الالكتروني ';
            $messages['password.required'] = 'يرجى ادخال كلمة المرور ';
        } else {
            return $this->send_response(400, "لم تقم بادخال رمز تسجيل الدخول او البريد الالكتروني", [], null, null);
        }

        $validator = Validator::make($request, $rules, $messages);

        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }


        if (isset($request['email'])) {

            $user = User::where("email", $request["email"])->first();

            // if ($user->mac_address != $request['mac_address']) {
            //     return $this->send_response(400, 'لايمكن تسجيل الدخول لهذا الجهاز', [], null, null);
            // }

            if (auth()->attempt(array('email' => $request['email'], 'password' => $request['password']))) {
                $user = auth()->user();
                if ($user->account_status == 1) {
                    $token = $user->createToken('educational_platform')->accessToken;
                    return $this->send_response(200, 'تم تسجيل الدخول بنجاح', [], $user, $token);
                } elseif ($user->account_status == 2) {
                    return $this->send_response(400, 'تم حظر حسابك يرجى التواصل مع المالك', [], null, null);
                } else {
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
                    $subject = 'التحقق من البريد الالكتروني';
                    Mail::to($user->email)->send(new EducationalMail($mail_data, $subject));

                    return $this->send_response(200, 'يرجى تاكيد عنوان بريدك الالكتروني', [], $user, null);
                }
            } else {
                return $this->send_response(400, "ادخلت اسم مستخدم او كلمة مرور غير صحيحة", [], null, null);
            }
        } elseif (isset($request['login_code'])) {
            $user = User::where("login_code", $request["login_code"])->first();

            if (!$user) {
                return $this->send_response(400, "رمز تسجيل الدخول غير صحيح", [], null, null);
            }

            if (!isset($user->mac_address)) {
                $data = [];
                $data = [
                    'mac_address' => $request['mac_address'],
                ];
                $user->update($data);
            }

            if ($user->mac_address != $request['mac_address']) {
                return $this->send_response(400, 'لايمكن تسجيل الدخول لهذا الجهاز', [], null, null);
            }

            $token = $user->createToken('educational_platform')->accessToken;
            return $this->send_response(200, 'تم تسجيل الدخول بنجاح', [], $user, $token);
        }

    }

    public function addUsers()
    {
        $data = [];
        $random_code = $this->random_code();
        $data = [
            'login_code' => $this->create_login_code(),
            'random_code' => $this->random_code(),
            'user_type' => 2,
            'account_status' => 1,
        ];
        $user = User::create($data);

        return $this->send_response(200, 'تم انشاء حساب جديد', [], User::find($user->id));
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
            $data['image'] = $this->uploadPicture($request['image'], '/images/users/');
        }
        $user = User::create($data);
        $mail_data = [
            "title" => "مرحبا " . $request['email'],
            "body" => " رمز التحقق الخاص بك :" . $random_code,
        ];
        $subject = 'التحقق من البريد الالكتروني';
        Mail::to($request['email'])->send(new EducationalMail($mail_data, $subject));
        return $this->send_response(200, 'يرجى التحقق من البريد الالكتروني', [], User::find($user->id));
    }

    public function getUser()
    {
        $user = User::find(auth()->user()->id);
        return $this->send_response(200, 'تم احضار معلومات المستخدم', [], $user);
    }

    public function getUsers()
    {

        $users = User::select("*");
        if (isset($_GET["query"])) {
            $users = $this->search($users, 'users');
        }
        if (isset($_GET["filter"])) {
            $filter = json_decode($_GET["filter"]);
            $users = $this->filter($users, $_GET["filter"]);
        }

        if (isset($_GET["order_by"])) {
            $users = $this->order_by($users, $_GET);
        }
        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($users->orderBy("created_at", "ASC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم احضار جميع المستخدمين', [], $res["model"], null, $res["count"]);
    }


    public function sendCodeAgain(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
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
        $subject = 'التحقق من البريد الالكتروني';
        Mail::to($user->email)->send(new EducationalMail($mail_data, $subject));
        return $this->send_response(200, 'تم اعادة ارسال رمز التحقق', [], null, null);
    }
    public function emailVerificationRegister(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:users,id',
            'random_code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
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
            'id' => 'required|exists:users,id',
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
            'image' => $this->uploadPicture($request['image'], '/images/users/')
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
                "title" => "مرحبا " . $request["email"],
                "body" => " رمز التحقق الخاص بك :" . $random_code,
            ];
            $subject = ' اعادة تعين البريد الالكتروني';
            Mail::to($request['email'])->send(new EducationalMail($mail_data, $subject));

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
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
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
    public function sendCodeAgainUpdate(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            "email" => "required|string|unique:users,email," . $request['email'],
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::find($request["email"]);
        $data = [];
        $random_code = $this->random_code();
        $data = [
            'random_code' => $random_code,
        ];
        $user->update($data);
        $mail_data = [
            "title" => "مرحبا " . $request["email"],
            "body" => " رمز التحقق الخاص بك :" . $random_code,
        ];
        $subject = 'التحقق من البريد الالكتروني';
        Mail::to($request["email"])->send(new EducationalMail($mail_data, $subject));
        return $this->send_response(200, 'تم اعادة ارسال رمز التحقق', [], null, null);
    }

    public function blockUser(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::find($request["id"]);

        $data = [];
        $data["account_status"] = 2;
        $user->update($data);

        foreach ($user->tokens as $token) {
            $token->revoke();
            $token->delete();
        }
        return $this->send_response(200, 'تم حظر المستخدم بنجاح', [], User::find($user->id), null);
    }
    public function openUser(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::find($request["id"]);

        $data = [];
        $data["account_status"] = 1;
        $user->update($data);
        return $this->send_response(200, 'تم تفعيل المستخدم بنجاح', [], User::find($user->id), null);
    }
    public function userUpgrade(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::find($request["id"]);

        $data = [];
        $data["user_type"] = 1;
        $user->update($data);
        return $this->send_response(200, 'تم ترقية المستخدم بنجاح', [], User::find($user->id), null);
    }
    public function CancelUserUpgrade(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::find($request["id"]);

        $data = [];
        $data["user_type"] = 2;
        $user->update($data);
        return $this->send_response(200, 'تم الغاء ترقية المستخدم بنجاح', [], User::find($user->id), null);
    }
    public function logout(Request $request)
    {
        auth()->user()->token()->revoke();
        return $this->send_response(200, 'تم تسجيل الخروج بنجاح', [], [], );
    }

    public function sendCodeForgotPassword(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make(
            $request,
            [
            "email" => "required|string|exists:users,email",
        ],
            [
            'email.required' => 'حقل البريد الالكتروني مطلوب',
            'email.exists' => ' البريد الالكتروني الذي قمت بأدخاله غير موجود',
        ]
        );
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::where("email", $request["email"])->first();
        $data = [];
        $random_code = $this->random_code();
        $data = [
            'random_code' => $random_code,
        ];
        $user->update($data);
        $mail_data = [
            "title" => "مرحبا " . $request["email"],
            "body" => " رمز التحقق الخاص بك :" . $random_code,
        ];
        $subject = 'اعادة تعين كلمة المرور';
        Mail::to($request["email"])->send(new EducationalMail($mail_data, $subject));
        return $this->send_response(200, 'تم ارسال رمز التحقق', [], null, null);
    }

    public function checkCodeForgotPassword(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make(
            $request,
            [
                'random_code' => 'required',
            "email" => "required|string|exists:users,email",
            "email" => "required|string|exists:users,email",
        ],
            [
            'random_code.required' => 'لم تدخل رمز التحقق',
            'email.required' => 'حقل البريد الالكتروني مطلوب',
            'email.exists' => ' البريد الالكتروني الذي قمت بأدخاله غير موجود',
        ]
        );
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::where("email", $request["email"])->first();
        if ($request["random_code"] == $user->random_code) {

            return $this->send_response(200, 'قم بتغير كلمة المرور', null, $user->id);
        } else {
            return $this->send_response(400, 'ادخلت رمز تحقق غير صحيح', [], null);
        }
    }


    public function resetPassword(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make(
            $request,
            [
              'id' => 'required|exists:users,id',
                "password" => "required|string|max:255|min:8",
        ],
            [
            'id.required' => 'معرف المستخدم مطلوب',
            'id.exists' => 'معرف المستخدم غير متوفر',
            'password.required' => 'حقل كلمة المرور الجديده مطلوبه',
            'password.max' => 'لقد تجاوزت الحد الاعلى لعدد احرف كلمة المرور',
            'password.min' => 'الحد الادنى لعدد احرف كلمة المرور 8',
        ]
        );
        if ($validator->fails()) {
            return $this->send_response(400, 'فشل العملية ', $validator->errors(), []);
        }
        $user = User::find($request["id"]);
        $user->update([
            'password' => bcrypt($request['password'])
        ]);

        return $this->send_response(200, 'تم تغير كلمة المرور بنجاح', [], []);
    }

}
