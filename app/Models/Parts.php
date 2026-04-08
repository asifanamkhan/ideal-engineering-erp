<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parts extends Model
{
    protected $table = "parts";

    protected $fillable = [
        'name',
        'price',
        'status',
        'model',
        'brand',
        'description',
    ];
}
