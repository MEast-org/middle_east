<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'photo',
        'gender',
        'birthday',
        'country_id',
        'region_id',
        'password',
        'verification_code',
        'code_expires_at',
        'verified_at',
        'fcm_token',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

     protected $hidden = [
        'password',
        'verification_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(country::class,'country_id','id');
    }

    // علاقة مع جدول المناطق
    public function region()
    {
        return $this->belongsTo(region::class,'region_id','id');
    }
    public function ads()
    {
        return $this->morphMany(ads::class, 'publisher');
    }
    public function opportunities()
    {
        return $this->morphMany(job_opportunity::class, 'publisher');
    }
       //فرص العمل التي قدمت عليهم
        public function applications()
    {
        return $this->morphMany(applicant::class, 'applicant');
    }

    public function auctions()
    {
        return $this->morphMany(auction::class, 'publisher');
    }

        public function favorites()
    {
        return $this->morphMany(favorite::class, 'favoriter');
    }


    // Methods for JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // التحقق من انتهاء صلاحية كود التحقق
    public function isVerificationCodeExpired()
    {
        return $this->code_expires_at && now()->gt($this->code_expires_at);
    }

    // التحقق من اكتمال تسجيل المستخدم
    public function isRegistrationComplete()
    {
        return $this->verified_at && $this->password;
    }

}
