<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class News extends Model
{
    use HasFactory,Uuids;
    protected $guarded = [];

    protected $with = ['images'];

    public function images()
    {
        return $this->morphMany(Images::class,'imageable');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }
}
