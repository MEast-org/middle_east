<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use App\Models\job_opportunity;

class category extends Model
{
    use HasFactory;
    use NodeTrait;

    protected $fillable = [
        'ar_name',
        'en_name',
        'icon',
        'sort_order',
        'parent_id',
        'state'
    ];

    protected $attributes = [
        'state' => 'active',
    ];

    public function scopeActive($query)
    {
        return $query->where('state', 'active');
    }

    protected static function booted()
    {
        // عند الإنشاء: تعيين sort_order = id (إذا لم يتم تحديده)
        static::created(function ($model) {
            if (empty($model->sort_order)) {
                $model->sort_order = $model->id;
                $model->save();
            }
        });

        // عند التحديث: إعادة ترتيب العناصر في نفس المستوى
        static::updating(function ($model) {
            if ($model->isDirty('sort_order')) {
                self::reorderSiblings($model);
            }
        });
    }

    protected static function reorderSiblings($model)
    {
        // نأخذ الأشقاء في نفس المستوى (نفس parent_id)
        $siblings = self::where('parent_id', $model->parent_id)
            ->where('id', '!=', $model->id)
            ->where('sort_order', '>=', $model->sort_order)
            ->orderBy('sort_order')
            ->get();

        $currentOrder = $model->sort_order;

        foreach ($siblings as $sibling) {
            $currentOrder++;
            $sibling->update(['sort_order' => $currentOrder]);
        }
    }

    public function opportunities()
    {
        return $this->hasMany(job_opportunity::class, 'category_id', 'id');
    }

     // علاقة واحدة مع الحقول المخصصة
     public function customfields()
     {
         return $this->hasMany(custom_field::class,'category_id', 'id')->orderBy('created_at');
     }

     // للوصول المباشر للقيم عبر العلاقة
     public function fieldvalues()
     {
         return $this->hasManyThrough(
            custom_field_value::class,
             custom_field::class
         );
        }

        public function ads()
    {
        return $this->hasMany(ads::class, 'category_id', 'id');
    }


}
