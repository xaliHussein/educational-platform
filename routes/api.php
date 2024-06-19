<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\BannersController;
use App\Http\Controllers\EnrollmentsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::controller(EnrollmentsController::class)->group(function () {
    Route::get('createEnrollment', 'createEnrollment');
});

Route::get('/redirect', [App\Http\Controllers\EnrollmentsController::class, 'redirect'])->name('redirect');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

route::post("login", [UsersController::class, "login"]);
route::post("register", [UsersController::class, "register"]);
route::post("code_verification", [UsersController::class, "emailVerificationRegister"]);
route::post("send_code_again", [UsersController::class, "sendCodeAgain"]);

Route::middleware(['auth:api'])->group(function () {


    Route::middleware('admin_educational_platform')->group(function () {
        Route::controller(CoursesController::class)->group(function () {
            Route::post('add_courses', 'addCourses');
            Route::put('edit_courses', 'editCourses');
            Route::delete('delete_courses', 'deleteCourses');
        });

    });
    Route::controller(CoursesController::class)->group(function () {
        Route::get('get_courses', 'getCourses');
    });
    Route::controller(CourseCategoryController::class)->group(function () {
        Route::get('get_courses_category', 'getCourseCategory');
    });
    Route::controller(BannersController::class)->group(function () {
        Route::get('get_banners', 'getBanners');
    });
    Route::controller(LessonsController::class)->group(function () {
        Route::get('get_lessons', 'getLessons');
    });
    Route::controller(EnrollmentsController::class)->group(function () {
        Route::post('check_enrollments_lessons', 'checkEnrollmentsLessons');
    });
    Route::controller(UsersController::class)->group(function () {
        Route::get('get_user', 'getUser');
        Route::put('update_image_user', 'updateImageUser');
        Route::put('update_user', 'updateUser');
        Route::put('update_password_user', 'updatePasswordUser');
        Route::put('update_email_user', 'updateEmailUser');
        Route::put('email_verification_update', 'emailVerificationUpdate');
    });

    // Route::get('/http://localhost:8080/available-courses', function () {
    //     return redirect('http://localhost:8080/available-courses')->name('redirect');
    // });

});
