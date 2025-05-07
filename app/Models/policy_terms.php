<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class policy_terms extends Model
{
    protected $fillable = ['key', 'locale', 'title', 'content'];
}
