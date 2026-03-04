<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id','candidate_id','started_at','submitted_at',
        'time_taken_seconds','auto_score','manual_score','total_score','status','meta'
      ];
      protected $casts = ['meta' => 'array','started_at'=>'datetime','submitted_at'=>'datetime'];

      public function exam(){ return $this->belongsTo(Exam::class); }
      public function candidate(){ return $this->belongsTo(Candidate::class); }
      public function answers(){ return $this->hasMany(ExamAnswer::class); }
}
