<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class PurchaseCode extends Model
{
    use HasFactory,Uuids;

    protected $guarded = [];

    protected $with = ['Course','Teacher'];
    public function Course()
    {
        return $this->belongsTo(Course_Category::class, 'category_id');
    }
    public function Teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
