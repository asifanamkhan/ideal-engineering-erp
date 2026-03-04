<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;
    protected $casts = ['options' => 'array'];
    protected $fillable = ['category_id','type','question','options','correct_answer','marks','explain'];

    public function category(){ return $this->belongsTo(Category::class); }
    public function exams(){ return $this->belongsToMany(Exam::class, 'exam_question'); }
}
