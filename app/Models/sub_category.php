<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\job_category;

class sub_category extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'en_name',
        'ar_name'
    ];

    public function category()
    {
        return $this->belongsTo(job_category::class, 'category_id', 'id');
    }
}
