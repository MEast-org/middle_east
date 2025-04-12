<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\country;
use App\Models\job_opportunity;
use App\Models\company;

class region extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'en_name',
        'ar_name'
    ];

    // العلاقة مع جدول الدول
    public function country()
    {
        return $this->belongsTo(country::class,'country_id','id');
    }

    public function opportunities()
    {
        return $this->hasMany(job_opportunity::class, 'region_id', 'id');
    }
    public function companies()
    {
        return $this->hasMany(company::class, 'region_id', 'id');
    }

}
