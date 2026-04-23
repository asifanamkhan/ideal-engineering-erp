<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $table = "sizes";

    protected $fillable = [
        'name',
        'status',
        'description',
        'is_default'
    ];
}
