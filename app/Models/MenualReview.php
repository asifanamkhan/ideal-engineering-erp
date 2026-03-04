<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenualReview extends Model
{
    protected $fillable = ['exam_answer_id','reviewed_by','marks_awarded','comments'];
    public function examAnswer(){ return $this->belongsTo(ExamAnswer::class, 'exam_answer_id'); }
}
