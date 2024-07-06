<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\User;
use App\Models\Lessons;
use Carbon\CarbonPeriod;
use App\Traits\Pagination;
use App\Models\Enrollments;
use App\Traits\UploadImage;
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
    use SendResponse, UploadImage, Pagination;

    public function getStatistics()
    {
        $firstDayOfFirstMonth = Carbon::now()->startOfYear();
        $firstMonthWithFirstDay = $firstDayOfFirstMonth->format('Y-m-d');
        $lastDayOfLastMonth = Carbon::now()->endOfYear();
        $lastMonthWithLastDay = $lastDayOfLastMonth->format('Y-m-d');

        $enrollments_six_months = Enrollments::select([
            DB::raw('DATE(created_at) AS date'),
            DB::raw('COUNT(*) AS count')
        ])->whereBetween('created_at', [$firstDayOfFirstMonth, $lastMonthWithLastDay])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get()
            ->toArray();
        $salesChartByMonth = [];
        $lastSixMonths = CarbonPeriod::create($firstMonthWithFirstDay, '1 month', $lastMonthWithLastDay);
        foreach ($enrollments_six_months as $data) {
            foreach ($lastSixMonths as $date) {
                $dateString = $date->format('F');
                if (!isset($salesChartByMonth[$dateString])) {
                    $salesChartByMonth[$dateString] = 0;
                }
            }
            if (isset($salesChartByMonth[$dateString])) {
                 $date = date('F', strtotime($data['date']));
                $salesChartByMonth[$date] = $data['count'];
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
        $course_category = Course_Category::select("*")->count();
        $enrollments = Enrollments::select("*")->count();
        $enrollments_cash = Enrollments::where("payment_type",0)->count();
        $enrollments_zine_cash = Enrollments::where("payment_type",1)->count();
        $enrollments_type = [$enrollments_cash,$enrollments_zine_cash];
        $result = [];
        $result = [
            'users' => $users,
            'teachers' => $teachers,
            'lessons' => $lessons,
            'course_category' => $course_category,
            'enrollments' => $enrollments,
            'enrollments_type' => $enrollments_type,
            'resulte_sixe_months' => $resulte_sixe_months,
        ];
        return $this->send_response(200, 'تم احضار الاحصائيات بنجاح', [], $result);
    }
}
