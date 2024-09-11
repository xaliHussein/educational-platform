<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory,Uuids;
    protected $guarded = [];
    protected $with = ['choices'];

    public function choices()
    {
        return $this->hasMany(Choice::class,'question_id');
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
            $question->choices()->delete();
        });
    }
}
