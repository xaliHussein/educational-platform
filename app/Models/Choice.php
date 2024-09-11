<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Choice extends Model
{
    use HasFactory,Uuids;
    protected $guarded = [];


    public function question()
    {
        return $this->belongsTo(Question::class);
    }


}
