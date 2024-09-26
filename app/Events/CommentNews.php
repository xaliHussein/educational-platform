<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentNews
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $newsId;

    public function __construct($comment, $newsId)
    {
        $this->comment = $comment;
        $this->newsId = $newsId;
    }

    // Use PrivateChannel to ensure authorization
    public function broadcastOn()
    {
        return new PrivateChannel('news.' . $this->newsId);
    }
}
