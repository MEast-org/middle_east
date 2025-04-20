<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class auction_images extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'image_path',
    ];


    public function auction()
    {
        return $this->belongsTo(auction::class,'auction_id','id');
    }
}
