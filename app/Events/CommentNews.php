<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentNews implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $newsId;
    public $studentId;

    public function __construct($comment, $newsId,$studentId)
    {
        $this->comment = $comment;
        $this->newsId = $newsId;
        $this->studentId = $studentId;
    }

    // Use PrivateChannel to ensure authorization
      /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */

    public function broadcastOn()
    {
        return new PrivateChannel('news.' . $this->newsId . '.' . $this->studentId);
    }
}
