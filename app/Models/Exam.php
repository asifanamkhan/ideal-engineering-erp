<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title','description','total_questions','duration_minutes',
        'randomize_questions','randomize_options','starts_at','ends_at','is_published','allow_resume'
    ];

    protected $dates = ['starts_at','ends_at'];

    public function questions(){
        return $this->belongsToMany(Question::class, 'exam_question')->withTimestamps()->withPivot('order');
    }

    public function candidates(){
        return $this->belongsToMany(Candidate::class, 'exam_candidate')->withTimestamps();
    }

    public function attempts(){
        return $this->hasMany(ExamAttempt::class);
    }
}
