<!DOCTYPE html>
<html dir="rtl" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحميل ملف بي دي إف</title>
    <style>
        @font-face {
            font-family: 'arabic-font';
            src: url('./fonts/Tajawal-Regular.ttf') format('truetype');
        }

        body {

            font-family: 'arabic-font';
        }

        .table {
            border-collapse: collapse;
            text-align: center;
        }

        .table th {

            border-top: 2px solid #624fc6;
            border-bottom: 2px solid #624fc6;
            padding: 0.9em 2em;
        }

        table td {
            padding: 0.9em 3em;
            border-top: 2px solid #624fc6;
            border-bottom: 2px solid #624fc6;
        }

        h3 {
            text-align: center;
        }

        table {
            margin: auto;
        }

        p {
            background-color: white;
            text-align: center;
        }

        .Copyright {
            text-decoration: none;
        }
    </style>
</head>
@php
    use Carbon\Carbon;
    $currentDate = Carbon::now();
@endphp

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 py-3">
                <h3>منصة تعليمي</h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">الاسم</th>
                            <th scope="col">رقم الطلب</th>
                            <th scope="col">الكورس</th>
                            <th scope="col">نوع الدفع</th>
                            <th scope="col">مبلغ الدفع </th>
                            <th scope="col">قيمة الخصم</th>
                            <th scope="col">التاريخ</th>

                        </tr>
                    </thead>
                    <tbody>


                        <tr>
                            <th scope="">
                                <h3>{{ $enrollment->user->name }}</h3>
                            </th>
                            <td>
                                <h3>#{{ $enrollment->order_id }}</h3>
                            </td>
                            <td>
                                <h3>{{ $enrollment->category->title }}</h3>
                            </td>

                            <td>
                                <h3
                                    @if ($enrollment->status == 0) >نقد</h3>
                                    <h3 @elseif ($enrollment->status == 1)> زين كاش</h3 @endif>
                            </td>
                            <td>
                                <h3>د.ع{{ $enrollment->price }}</h3>
                            </td>

                            <td>
                                <h3 style="color:green"
                                    @if ($enrollment->offer != null) >{{ $enrollment->offer }}%</h3>
                                <h3 style="color:red" @else>
                                       لايوجد
                                </h3 @endif>
                            </td>

                            <td>
                                <h3>{{ $enrollment->subscription_time }}</h3>
                            </td>
                        </tr>


                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
