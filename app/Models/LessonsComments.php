<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonsComments extends Model
{
    use HasFactory;
    use Uuids;

    protected $guarded = [];
    protected $with = ['User'];
    public function Lessons()
    {
        return $this->belongsTo(Lessons::class, 'lessons_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parentComment()
    {
        return $this->belongsTo(LessonsComments::class, 'parent_comment_id');
    }

    public function children()
    {
        return $this->hasMany(LessonsComments::class, 'parent_comment_id');
    }


    public static function boot()
    {
        parent::boot();

        // Generate UUID for question id
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // Automatically delete related choices when a question is deleted
        static::deleting(function ($question) {
            $question->children()->delete();
        });
    }
}
