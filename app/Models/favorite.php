<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class favorite extends Model
{
    protected $fillable = [
        'favoriter_id',
        'favoriter_type',
        'favorable_id',
        'favorable_type',
    ];


    public function favoriter()
    {
        return $this->morphTo();
    }

    public function favorable()
    {
        return $this->morphTo();
    }
}
