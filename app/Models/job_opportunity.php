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
        'name',
        'publisher_type',
        'publisher_id',
        'category_id',
        'country_id',
        'region_id',
        'description',
        'expires_at',
        'type',
        'state',
        'min_salary',
        'max_salary',
        'starts_at',
        'social_links',
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
        'starts_at' => 'date',
        'social_links'=>'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('state', 'active');
    }

    // علاقة مع التصنيف
    public function category()
    {
        return $this->belongsTo(category::class, 'category_id', 'id')->withDefault([
            'name' => 'غير محدد',
            'id' => 0
        ]);
    }

    // // علاقة مع الشركة
    // public function company()
    // {
    //     return $this->belongsTo(company::class, 'company_id', 'id')->withDefault([
    //         'name' => 'غير محدد',
    //         'id' => 0
    //     ]);
    // }

     public function publisher()
        {
            return $this->morphTo();
        }

        public function favorites()
        {
            return $this->morphMany(favorite::class, 'favorable');
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

    public function applications()
    {
        return $this->hasMany(applicant::class, 'opportunity_id');
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
