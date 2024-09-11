<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriesQuestion extends Model
{
    use HasFactory,Uuids;
    protected $guarded = [];

    public function question()
    {
        return $this->hasMany(Question::class,'category_id');
    }


    public static function boot()
    {
        parent::boot();

        // Generate UUID on create
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // Automatically delete related questions and choices when a category is deleted
        static::deleting(function ($categories_question) {
            foreach ($categories_question->question as $question) {
                $question->choices()->delete(); // Delete related choices
                $question->delete();            // Delete the question
            }
        });
    }
}
