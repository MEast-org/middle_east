<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ads extends Model
{
    use HasFactory;

    protected $fillable = [
        'publisher_type',
        'publisher_id',
        'country_id',
        'region_id',
        'category_id',
        'latitude',
        'longitude',
        'views',
        'shares',
    ];

    public function publisher()
    {
        return $this->morphTo();
    }

    // العلاقة مع الدولة
    public function country()
    {
        return $this->belongsTo(country::class,'country_id','id');
    }

    // العلاقة مع المنطقة
    public function region()
    {
        return $this->belongsTo(region::class,'region_id','id');
    }

    // العلاقة مع الفئة
    public function category()
    {
        return $this->belongsTo(category::class,'category_id','id');
    }

    public function fieldvalues()
    {
        return $this->morphMany(custom_field_value::class, 'owner_table');
    }
        protected static function booted()
    {
        static::deleting(function ($ad) {
            custom_field_value::where('owner_table_type', 'ads')
                ->where('owner_table_id', $ad->id)
                ->delete();
        });
    }
    }
