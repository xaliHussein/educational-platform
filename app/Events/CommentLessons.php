<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentLessons  implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $comment;
    public $lessonId;
    public $studentId;

    public function __construct($comment, $lessonId, $studentId)
    {
        $this->comment = $comment;
        $this->lessonId = $lessonId;
        $this->studentId = $studentId;
    }

    // Use PrivateChannel to ensure authorization
    public function broadcastOn()
    {
        return new PrivateChannel('lesson.' . $this->lessonId . '.' . $this->studentId);
    }
}
