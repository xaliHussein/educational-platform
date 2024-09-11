<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BannersController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\EnrollmentsController;
use App\Http\Controllers\PurchaseCodeController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\QuestionController;

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




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::get('pamentCheck', [EnrollmentsController::class, "pamentCheck"])->name('redirect');

route::post("login", [UsersController::class, "login"]);
route::post("register", [UsersController::class, "register"]);
route::post("code_verification", [UsersController::class, "emailVerificationRegister"]);
route::post("send_code_again", [UsersController::class, "sendCodeAgain"]);


route::post("send_code_forgot_password", [UsersController::class, "sendCodeForgotPassword"]);
route::post("check_code_forgot_password", [UsersController::class, "checkCodeForgotPassword"]);
route::post("reset_password", [UsersController::class, "resetPassword"]);


Route::middleware(['auth:api'])->group(function () {


    Route::middleware('admin_educational_platform')->group(function () {

        Route::controller(CoursesController::class)->group(function () {
            Route::post('add_courses', 'addCourses');
            Route::put('edit_courses', 'editCourses');
            Route::delete('delete_courses', 'deleteCourses');
        });

        Route::controller(NewsController::class)->group(function () {
            Route::post('add_news', 'addNews');
            Route::delete('delete_news', 'deleteNews');
        });

        Route::controller(UsersController::class)->group(function () {
            Route::get('get_users', 'getUsers');

            Route::post('add_users', 'addUsers');
            Route::put('block_user', 'blockUser');
            Route::put('open_user', 'openUser');
            Route::put('user_upgrade', 'userUpgrade');
            Route::put('cancel_user_upgrade', 'CancelUserUpgrade');
        });

        Route::controller(CourseCategoryController::class)->group(function () {
            Route::get('get_courses_category_dashboard', 'getCourseCategoryDashboard');
            Route::post('add_course_category', 'addCourseCategory');
            Route::put('edit_courses_category', 'editCoursesCategory');
            Route::delete('delete_courses_category', 'deleteCoursesCategory');
        });

        Route::controller(BannersController::class)->group(function () {
            Route::post('add_banners', 'addBanners');
            Route::put('edit_banners', 'editBanners');
            Route::delete('delete_banners', 'deleteBanners');
        });

        Route::controller(PurchaseCodeController::class)->group(function () {
            Route::get('get_purchase_code', 'getPurchaseCode');
            Route::post('add_purchase_code', 'addPurchaseCode');
        });

        Route::controller(EnrollmentsController::class)->group(function () {
            Route::get('get_enrollments', 'getEnrollments');
        });
        Route::controller(LessonsController::class)->group(function () {
            Route::get('get_lessons_dashboard', 'getLessonsDashboard');
            Route::get('get_courses_category_lessons', 'getCoursesCategoryLessons');
            Route::post('add_lessons', 'addLessons');
            Route::put('edit_lessons', 'editLessons');
            Route::delete('delete_lessons', 'deleteLessons');
            Route::post('upload_vedio_lessons', 'uploadVedioLessons');
            Route::delete('delete_vedio_lessons', 'deleteVedioLessons');
        });

        Route::controller(QuestionController::class)->group(function () {

            Route::get('get_question', 'getQuestion');
            Route::post('add_question', 'addQuestion');
            Route::post('show_result_question', 'showResultQuestion');
            Route::delete('delete_question', 'deleteQuestion');
            Route::put('edit_question', 'editQuestion');

            Route::get('get_categories_question', 'getCategoriesQuestion');
            Route::post('add_categories_question', 'addCategoriesQuestion');
            Route::delete('delete_categories_question', 'deleteCategoriesQuestion');
            Route::put('edit_categories_question', 'editCategoriesQuestion');

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
        Route::post('buy_course_code', 'buyCourseCode');
        Route::post('payment', 'makePayment');
        Route::get('pamentCheck', 'pamentCheck')->name('redirect')->withoutMiddleware('auth:api');
    });
    Route::controller(StatisticsController::class)->group(function () {
        Route::get('get_statistics', 'getStatistics');
    });

    Route::controller(NewsController::class)->group(function () {
        Route::get('get_news', 'getNews');
    });
    Route::controller(UsersController::class)->group(function () {
        Route::get('get_user', 'getUser');
        Route::put('update_image_user', 'updateImageUser');
        Route::put('update_user', 'updateUser');
        Route::put('update_password_user', 'updatePasswordUser');
        Route::put('update_email_user', 'updateEmailUser');
        Route::put('email_verification_update', 'emailVerificationUpdate');
        Route::put('send_code_again_update', 'sendCodeAgainUpdate');
        Route::post('logout', 'logout');
    });


    Route::controller(CommentsController::class)->group(function () {
        Route::get('get_user_comments', 'getUserComments');
        Route::post('add_comment', 'addComment');
        Route::post('reply_comment', 'replyComment');
        Route::delete('delete_comment', 'deleteComment');

    });
});
