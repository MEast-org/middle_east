<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\category;
use App\Models\company;
use App\Models\country;
use App\Models\region;

class job_opportunity extends Model
{
    use HasFactory;
    protected $fillable = [
        'en_name',
        'ar_name',
        'category_id',
        'company_id',
        'country_id',
        'region_id',
        'description',
        'expires_at',
        'type',
        'views',
        'shares',
        'applicants'
    ];

    protected $attributes = [
        'views' => 0,
        'shares' => 0,
        'applicants' => 0
    ];
    protected $casts = [
        'expires_at' => 'date',
    ];

    // علاقة مع التصنيف
    public function category()
    {
        return $this->belongsTo(category::class, 'category_id', 'id')->withDefault([
            'name' => 'غير محدد',
            'id' => 0
        ]);
    }

    // علاقة مع الشركة
    public function company()
    {
        return $this->belongsTo(company::class, 'company_id', 'id')->withDefault([
            'name' => 'غير محدد',
            'id' => 0
        ]);
    }

    // علاقة مع الدولة
    public function country()
    {
        return $this->belongsTo(country::class, 'country_id', 'id')->withDefault([
            'name' => 'غير محدد',
            'id' => 0
        ]);
    }

    // علاقة مع المنطقة
    public function region()
    {
        return $this->belongsTo(region::class, 'region_id', 'id')->withDefault([
            'name' => 'غير محدد',
            'id' => 0
        ]);
    }
    // public function fieldvalues()
    // {
    //     return $this->hasMany(custom_field_value::class, 'opportunity_id', 'id');
    // }

        public function fieldvalues()
    {
        return $this->morphMany(custom_field_value::class, 'owner_table');
    }

    // زيادة عدد المشاهدات
    public function incrementViews()
    {
        $this->increment('views');
    }

    // زيادة عدد المشاركات
    public function incrementShares()
    {
        $this->increment('shares');
    }

    // زيادة عدد المتقدمين
    public function incrementApplicants()
    {
        $this->increment('applicants');
    }
}
