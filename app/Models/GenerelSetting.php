<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerelSetting extends Model
{
    protected $fillable = ['company_name', 'email', 'phone', 'logo', 'address'];
}
