<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamCategory extends Model
{
    use SoftDeletes;
    protected $fillable = ['name','description'];
    public function questions(){ return $this->hasMany(Question::class); }
}
