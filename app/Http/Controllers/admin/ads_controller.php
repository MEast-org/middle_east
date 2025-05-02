<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\admin;
use App\Models\company;
use App\Models\user;
use App\Models\country;
use App\Models\region;
use App\Models\ads;
use App\Models\custom_field;
use App\Models\custom_field_value;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;

class ads_controller extends Controller
{

        /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }

    public function all_ads()
    {
        $ads = ads::with(['country', 'region', 'category.ancestors','publisher','fieldvalues.field'])->paginate(10); // استخدام التصفح
        return response()->json([
            'ads' => $ads
        ]);
    }

    /**
     * عرض تفاصيل إعلان معين
     */
    public function view_ad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:ads,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $ad =ads::with(['country', 'region', 'category.ancestors','publisher','fieldvalues.field'])->find($request->id);
        return response()->json([
            'ad' => $ad
        ]);
    }

    /**
     * إضافة إعلان جديد
     */
    public function add_ad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'publisher_type' => 'required|in:user,company',
            'publisher_id' => 'required|exists:' . ($request->publisher_type == 'user' ? 'users' : 'companies') . ',id',
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'category_id' => 'required|exists:categories,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|string',
            'views' => 'nullable|integer',
            'shares' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $ad = ads::create($validator->validated());

        return response()->json([
            'message' => 'Ad created successfully',
            'ad' => $ad
        ], 201);
    }

    /**
     * تعديل إعلان موجود
     */
    public function update_ad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:ads,id',
            'publisher_type' => 'sometimes|required|in:user,company',
            'publisher_id' => 'sometimes|required|exists:' . ($request->publisher_type == 'user' ? 'users' : 'companies') . ',id',
            'country_id' => 'sometimes|nullable|exists:countries,id',
            'region_id' => 'sometimes|nullable|exists:regions,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'description' => 'sometimes|nullable|string',
            'social_links' => 'sometimes|nullable|array',
            'social_links.*' => 'nullable|string',

            'state' => 'sometimes|required|in:inactive,active',
            'views' => 'sometimes|nullable|integer',
            'shares' => 'sometimes|nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $ad = ads::find($request->id);

        $oldCategoryId = $ad->category_id;
        // إذا أرسل category_id في الريكوست
        if ($request->has('category_id')) {
            $newCategoryId = $request->input('category_id');

            // إذا تغيّرت الفئة
            if ($oldCategoryId != $newCategoryId) {
                // حذف القيم المرتبطة بالفئة القديمة
                $oldFieldIds =custom_field::where('category_id', $oldCategoryId)->pluck('id');

                custom_field_value::where('owner_table_type', 'ads')
                    ->where('owner_table_id', $ad->id)
                    ->whereIn('custom_field_id', $oldFieldIds)
                    ->delete();
            }
        }





        $ad->update($validator->validated());

        return response()->json([
            'message' => 'Ad updated successfully',
            'ad' => $ad
        ]);
    }

    /**
     * حذف إعلان
     */
    public function delete_ad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:ads,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $ad = ads::find($request->id);
        $ad->delete();

        return response()->json(['message' => 'Ad deleted successfully']);
    }


public function filter_ads(Request $request)
{
    $query = ads::query();

    // فلترة حسب البلد
    if ($request->filled('country_id')) {
        $query->where('country_id', $request->country_id);
    }

    // فلترة حسب المدينة (المنطقة)
    if ($request->filled('region_id')) {
        $query->where('region_id', $request->region_id);
    }

    // فلترة حسب الفئة
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->filled('start_date')) {
        $dateFrom = Carbon::parse($request->start_date)->format('Y-m-d');
        $query->whereDate('created_at', '>=', $dateFrom);
    }

    if ($request->filled('end_date')) {
        $dateTo = Carbon::parse($request->end_date)->format('Y-m-d');
        $query->whereDate('created_at', '<=', $dateTo);
    }

    // إحضار العلاقات
    $ads = $query->with(['country', 'region', 'category.ancestors','publisher','fieldvalues.field'])->paginate(10);

    return response()->json([
        'ads' => $ads
    ]);
}

}
