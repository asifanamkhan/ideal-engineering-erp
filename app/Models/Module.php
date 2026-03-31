<?php
// app/Models/Module.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Module extends Model
{
    protected $fillable = ['name', 'branch_id', 'description'];

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}