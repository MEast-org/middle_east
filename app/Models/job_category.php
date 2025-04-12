<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\sub_category;


class job_category extends Model
{
    use HasFactory;
    protected $fillable = [
        'en_name',
        'ar_name',
        'icon',
        'state'
    ];

    public function subcategories()
    {
        return $this->hasMany(sub_category::class, 'category_id', 'id');
    }
}
