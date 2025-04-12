<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class custom_field extends Model
{
    use HasFactory;



    protected $fillable = [
        'category_id',
        'ar_name',
        'en_name',
        'type',
        'min_length',
        'max_length',
        'options',
        'is_required',
    ];

    protected $casts = [
        'options' => 'array'
    ];
       // علاقة مع الفئة
       public function category()
       {
           return $this->belongsTo(category::class , 'category_id' , 'id');
       }

       // علاقة مع القيم
       public function values()
       {
           return $this->hasMany(custom_field_value::class , 'custom_field_id' , 'id');
       }

}
