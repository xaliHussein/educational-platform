<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentLessons
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $lessonId;

    public function __construct($comment, $lessonId)
    {
        $this->comment = $comment;
        $this->lessonId = $lessonId;
    }

    // Use PrivateChannel to ensure authorization
    public function broadcastOn()
    {
        return new PrivateChannel('lesson.' . $this->lessonId);
    }
}
