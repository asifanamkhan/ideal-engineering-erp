<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    protected $fillable = ['exam_attempt_id','question_id','answer','is_correct','obtained_marks','raw'];
    protected $casts = ['raw'=>'array'];

    public function attempt(){ return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id'); }
    public function question(){ return $this->belongsTo(Question::class); }
    public function manualReview(){ return $this->hasOne(ManualReview::class); }
}
