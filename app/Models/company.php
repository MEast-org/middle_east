<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\job_opportunity;
use App\Models\country;
use App\Models\region;

class company extends Authenticatable implements JWTSubject
{
    use Notifiable,HasFactory;

    protected $fillable = [
        'ar_name',
        'en_name',
        'email',
        'password',
        'country_id',
        'region_id',
        'phone',
        'logo',
        'trade_log',
        'state'
    ];


    // JWT التوابع المطلوبة لـ
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function opportunities()
    {
        return $this->hasMany(job_opportunity::class, 'company_id', 'id');
    }

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

}
