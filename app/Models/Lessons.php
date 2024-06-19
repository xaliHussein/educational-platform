<?php

namespace App\Models;

use App\Traits\Uuids;
use App\Models\Course_Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lessons extends Model
{
    use HasFactory,Uuids;

    protected $guarded = [];

    protected $with = ['Course'];
    public function Course()
    {
        return $this->belongsTo(Course_Category::class, 'category_id');
    }
}
