<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
class Course_Category extends Model
{
    use HasFactory,Uuids;

    protected $guarded = [];
    protected $with = ['User'];
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
