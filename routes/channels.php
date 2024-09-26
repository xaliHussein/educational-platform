<?php

use App\Models\News;
use App\Models\Lessons;
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


Broadcast::channel('news.{newsId}', function ($user, $newsId) {
    // Logic to check if the user can listen to this news's comments
    $news = News::find($postId);

    // Example: Only allow authenticated users who have access to the news
    if ($news && $user->canAccessPost($news)) {
        return true;
    }

    return false;  // Deny access if the user isn't authorized
});

Broadcast::channel('lesson.{lessonId}', function ($user, $lessonId) {
    // Logic to check if the user can listen to this news's comments
    $lesson = Lessons::find($lessonId);

    // Example: Only allow authenticated users who have access to the news
    if ($lesson && $user->canAccessPost($lesson)) {
        return true;
    }

    return false;  // Deny access if the user isn't authorized
});
