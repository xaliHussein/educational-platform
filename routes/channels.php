<?php

use App\Models\News;
use App\Models\Lessons;
use App\Models\Comments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('news.{newsId}.{studentId}', function ($user, $newsId, $studentId) {
    $news = News::find($newsId);

    return $user->id === $studentId || $user->id === $news->user_id;
});


Broadcast::channel('lesson.{lessonId}.{studentId}', function ($user, $lessonId, $studentId) {
    $lessons = Lessons::find($lessonId);
    return $user->id === $studentId || $user->id === $lessons->user_id;
});
