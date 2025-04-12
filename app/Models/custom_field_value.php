<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class custom_field_value extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_field_id',
        'opportunity_id',
        'value',
    ];
    protected $casts = [
        'value' => 'array'
    ];
 
     // علاقة مع الحقل المخصص
     public function field()
     {
         return $this->belongsTo(custom_field::class , 'custom_field_id' , 'id');
     }

     public function opportunity()
     {
         return $this->belongsTo(job_opportunity::class , 'opportunity_id' , 'id');
     }

     // للوصول للفئة عبر الحقل المخصص
     public function category()
     {
         return $this->field->category;
     }
}
