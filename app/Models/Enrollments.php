<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
class Enrollments extends Model
{
    use HasFactory,Uuids;

    protected $guarded = [];

    protected $with = ['User','Category','Teacher'];
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function Teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    public function Category()
    {
        return $this->belongsTo(Course_Category::class, 'category_id');
    }
}
