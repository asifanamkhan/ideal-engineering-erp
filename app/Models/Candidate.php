<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    public function attempts(){ return $this->hasMany(ExamAttempt::class); }
    public function exams(){ return $this->belongsToMany(Exam::class, 'exam_candidate'); }
}
