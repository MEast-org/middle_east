<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class applicant extends Model
{
    protected $fillable = [
        'applicant_id',
        'applicant_type',
        'opportunity_id',
        'name',
        'description',
        'cv',
    ];

    public function applicant()
    {
        return $this->morphTo();
    }

    public function opportunity()
    {
        return $this->belongsTo(job_opportunity::class,'opportunity_id','id');
    }
}
