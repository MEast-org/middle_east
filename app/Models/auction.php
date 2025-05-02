<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'country_id',
        'region_id',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'publisher_id',
        'publisher_type',
        'social_links',
        'status',
    ];


    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'social_links'=>'array'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }


    public function images()
    {
        return $this->hasMany(auction_images::class,'auction_id','id');
    }

    public function category()
    {
        return $this->belongsTo(category::class,'category_id','id');
    }

    public function country()
    {
        return $this->belongsTo(country::class,'country_id','id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class,'region_id','id');
    }

    public function publisher()
    {
        return $this->morphTo();
    }
}
