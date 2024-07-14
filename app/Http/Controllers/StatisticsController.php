<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Courses;
use App\Models\Lessons;
use Carbon\CarbonPeriod;
use App\Traits\Pagination;
use App\Models\Enrollments;
use App\Traits\UploadImage;
use App\Models\PurchaseCode;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use App\Mail\EducationalMail;
use App\Models\Course_Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class StatisticsController extends Controller
{
    use SendResponse;
    use UploadImage;
    use Pagination;

    public function getStatistics()
    {
        $firstDayOfFirstMonth = Carbon::now()->startOfYear();
        $firstMonthWithFirstDay = $firstDayOfFirstMonth->format('Y-m-d');
        $lastDayOfLastMonth = Carbon::now()->endOfYear();
        $lastMonthWithLastDay = $lastDayOfLastMonth->format('Y-m-d');

        $enrollments_six_months = Enrollments::where("status", 1)->select([
            DB::raw('DATE(created_at) AS date'),
            DB::raw('COUNT(*) AS count')
        ])->whereBetween('created_at', [$firstDayOfFirstMonth, $lastMonthWithLastDay])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->toArray();
        $salesChartByMonth = [];
        $lastSixMonths = CarbonPeriod::create($firstMonthWithFirstDay, '1 month', $lastMonthWithLastDay);

        foreach ($lastSixMonths as $date) {
            $dateString = $date->format('F');
            $salesChartByMonth[$dateString] = 0;

        }

        foreach ($enrollments_six_months as $data) {
            $date = date('F', strtotime($data['date']));
            if (isset($salesChartByMonth[$dateString])) {
                $salesChartByMonth[$date] += $data['count'];
            }
        }

        $data = [];
        $chart = [];
        foreach ($salesChartByMonth as $key => $val) {
            array_push($chart, $key);
            array_push($data, $val);
        }
        $resulte_sixe_months = [$data, $chart];

        $users = User::where("user_type", 2)->where('account_status', 1)->count();
        $teachers = User::where("user_type", 1)->orWhere("user_type", 0)->where('account_status', 1)->count();
        $lessons = Lessons::select("*")->count();
        $course = Courses::select("*")->count();
        $course_category = Course_Category::select("*")->count();
        $purchase_code = PurchaseCode::select("*")->count();
        $enrollments = Enrollments::where("status", 1)->count();
        $enrollments_cash = Enrollments::where("payment_type", 0)->where("status", 1)->count();
        $enrollments_zine_cash = Enrollments::where("payment_type", 1)->where("status", 1)->count();
        $enrollments_type = [$enrollments_cash,$enrollments_zine_cash];
        $result = [];
        $result = [
            'users' => $users,
            'teachers' => $teachers,
            'lessons' => $lessons,
            'course' => $course,
            'purchase_code' => $purchase_code,
            'course_category' => $course_category,
            'enrollments' => $enrollments,
            'enrollments_type' => $enrollments_type,
            'resulte_sixe_months' => $resulte_sixe_months,
        ];
        return $this->send_response(200, 'تم احضار الاحصائيات بنجاح', [], $result);
    }
}
