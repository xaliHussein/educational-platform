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


Broadcast::channel('news.{newsId}', function ($user, $newsId, $studentId) {
    // Check if the user is a professor
    $isProfessor = $user->user_type == 0 || $user->user_type == 1;

    if ($isProfessor) {
        // Professors can listen if they are responding to a specific student's comments
        return Comments::where('news_id', $newsId)
            ->where('is_professor', true) // Ensure it's a professor's comment
            ->where('user_id', $user->id) // Check if the professor is the one commenting
            ->whereIn('parent_comment_id', function ($query) use ($studentId) {
                $query->select('id')
                    ->from('comments')
                    ->where('news_id', $newsId)
                    ->where('user_id', $studentId); // Ensure it's the student's comment
            })
            ->exists();
    } else {
        // Students can listen only if they are the specific student
        return $user->id === (int)$studentId;
    }
});

Broadcast::channel('lesson.{lessonId}', function ($user, $lessonId, $studentId) {

    // Check if the user is a professor
    $isProfessor = $user->user_type == 0 || $user->user_type == 1;

    if ($isProfessor) {
        // Professors can listen if they are responding to a specific student's comments
        return Lessons::where('lessons_id', $lessonId)
            ->where('is_professor', true) // Ensure it's a professor's comment
            ->where('user_id', $user->id) // Check if the professor is the one commenting
            ->whereIn('parent_comment_id', function ($query) use ($studentId) {
                $query->select('id')
                    ->from('lessons_comments')
                    ->where('lessons_id', $lessonId)
                    ->where('user_id', $studentId); // Ensure it's the student's comment
            })
            ->exists();
    } else {
        // Students can listen only if they are the specific student
        return $user->id === (int)$studentId;
    }

});
