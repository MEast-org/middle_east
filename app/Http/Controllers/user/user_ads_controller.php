<?php

namespace App\Http\Controllers\user;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\company;
use App\Models\ads;
use App\Models\custom_field;
use App\Models\custom_field_value;
use Validator;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use App\Services\CustomFieldService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class user_ads_controller extends Controller
{
    public function __construct()
    {
        $this->middleware('user_company_auth');
    }

    public function my_ads()
    {
        $user = auth()->user();

        $ads = ads::where('publisher_type', $user instanceof \App\Models\company ? 'company' : 'user')
            ->where('publisher_id', $user->id)
            ->with(['country', 'region', 'category.ancestors', 'publisher', 'fieldvalues.field'])
            ->latest()
            ->paginate(request('page_size',10));

        return ResponseHelper::success('My ads retrieved successfully',$ads);
    }

    public function show_ad($id)
    {
        $user = auth()->user();

        $ad = ads::with(['country', 'region', 'category.ancestors', 'publisher', 'fieldvalues.field'])
            ->where('id', $id)
            ->where('publisher_type', $user instanceof \App\Models\company ? 'company' : 'user')
            ->where('publisher_id', $user->id)
            ->first();

        if (!$ad) {
            return ResponseHelper::error('Ad not found or you do not have permission to view it', null, 404);
        }

        return ResponseHelper::success('Ad retrieved successfully', $ad);
    }

    public function add_ad(Request $request)
    {
        $user = auth()->user();

        $validated = Validator::make($request->all(), [
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'category_id' => 'required|exists:categories,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|string',
        ]);

        if ($validated->fails()) {
            return ResponseHelper::returnValidationError($validated);
        }

        $data = $validated->validated();
        $data['publisher_type'] = $user instanceof \App\Models\company ? 'company' : 'user';
        $data['publisher_id'] = $user->id;

        try {
            // نبدأ المعاملة لضمان عدم إضافة شيء في حال فشل الحقول المخصصة
            DB::beginTransaction();

            $ad = ads::create($data);

            // معالجة الحقول المخصصة
            app(CustomFieldService::class)->handle($request, $ad->id, $data['category_id']);

            DB::commit();

            return ResponseHelper::success('Ad created successfully', $ad);
        } catch (ValidationException $e) {
            DB::rollBack();
            return ResponseHelper::returnValidationError($e);
        } catch (\Throwable $e) {
            DB::rollBack();
            return ResponseHelper::error('Something went wrong', $e->getMessage());
        }

        // return ResponseHelper::success('Ad created successfully',$ad);
    }

    // public function update_ad(Request $request, $id)
    // {
    //     $user = auth()->user();

    //     $validated = Validator::make($request->all(), [
    //         'country_id' => 'sometimes|nullable|exists:countries,id',
    //         'region_id' => 'sometimes|nullable|exists:regions,id',
    //         'category_id' => 'sometimes|required|exists:categories,id',
    //         'latitude' => 'sometimes|nullable|numeric',
    //         'longitude' => 'sometimes|nullable|numeric',
    //         'price' => 'sometimes|nullable|numeric',
    //         'description' => 'sometimes|nullable|string',
    //         'social_links' => 'sometimes|nullable|array',
    //         'social_links.*' => 'nullable|string',
    //     ]);

    //     if ($validated->fails()) {
    //         return ResponseHelper::returnValidationError($validated);
    //     }

    //     $ad = ads::find($id);

    //     if (!$ad || $ad->publisher_type !== (get_class($user) === \App\Models\company::class ? 'company' : 'user') || $ad->publisher_id !== $user->id) {
    //         return ResponseHelper::error('You do not have permission to edit this ad', null, 403);
    //     }

    //     $newCategoryId = $request->input('category_id', $ad->category_id);

    //     try {
    //         DB::beginTransaction();

    //         if ($request->has('category_id') && $ad->category_id != $request->category_id) {
    //             $oldFieldIds = custom_field::where('category_id', $ad->category_id)->pluck('id');
    //             custom_field_value::where('ad_id', $ad->id)->whereIn('custom_field_id', $oldFieldIds)->delete();
    //         }

    //         $ad->update($validated->validated());

    //         app(CustomFieldService::class)->handle($request, $ad->id, $newCategoryId);

    //         DB::commit();

    //         return ResponseHelper::success('Ad updated successfully', $ad);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         return ResponseHelper::returnValidationError($e);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         return ResponseHelper::error('Something went wrong', $e->getMessage());
    //     }


    // }

    public function update_ad(Request $request, $id)
{
    $user = auth()->user();

    $validated = Validator::make($request->all(), [
        'country_id' => 'sometimes|nullable|exists:countries,id',
        'region_id' => 'sometimes|nullable|exists:regions,id',
        'category_id' => 'sometimes|required|exists:categories,id',
        'latitude' => 'sometimes|nullable|numeric',
        'longitude' => 'sometimes|nullable|numeric',
        'price' => 'sometimes|nullable|numeric',
        'description' => 'sometimes|nullable|string',
        'social_links' => 'sometimes|nullable|array',
        'social_links.*' => 'nullable|string',
    ]);

    if ($validated->fails()) {
        return ResponseHelper::returnValidationError($validated);
    }

    $ad = ads::find($id);

    if (
        !$ad ||
        $ad->publisher_type !== (get_class($user) === \App\Models\company::class ? 'company' : 'user') ||
        $ad->publisher_id !== $user->id
    ) {
        return ResponseHelper::error('You do not have permission to edit this ad', null, 403);
    }

    // معرفة الفئة الجديدة أو الاحتفاظ بالفئة الحالية
    $newCategoryId = $request->has('category_id') ? $request->input('category_id') : $ad->category_id;

    try {
        DB::beginTransaction();

        // إذا تغيّرت الفئة، احذف الحقول القديمة فقط
        if ($ad->category_id != $newCategoryId) {
            $oldFieldIds = custom_field::where('category_id', $ad->category_id)->pluck('id');

            $oldFieldValues = custom_field_value::with('field')
                ->where('ad_id', $ad->id)
                ->whereIn('custom_field_id', $oldFieldIds)
                ->get();

            foreach ($oldFieldValues as $fieldValue) {
                if ($fieldValue->field && $fieldValue->field->type === 'file') {
                    if ($fieldValue->value) {
                        Storage::disk('public')->delete($fieldValue->value);
                    }
                }
                $fieldValue->delete();
            }
        }

        // تحديث باقي بيانات الإعلان
        $ad->update($validated->validated());

        // التعامل مع الحقول الجديدة بعد تعديل الفئة
        app(CustomFieldService::class)->handle($request, $ad->id, $newCategoryId);

        DB::commit();

        return ResponseHelper::success('Ad updated successfully', $ad);
    } catch (ValidationException $e) {
        DB::rollBack();
        return ResponseHelper::returnValidationError($e);
    } catch (\Throwable $e) {
        DB::rollBack();
        return ResponseHelper::error('Something went wrong', $e->getMessage());
    }
}


    public function delete_ad($id)
{
    $user = auth()->user();

    $ad = ads::find($id);

    if (
        !$ad ||
        $ad->publisher_type !== (get_class($user) === \App\Models\company::class ? 'company' : 'user') ||
        $ad->publisher_id !== $user->id
    ) {
        return ResponseHelper::error('You do not have permission to delete this ad', null, 403);
    }

    // حذف القيم الخاصة بالحقول، مع حذف الملفات إن وجدت
    foreach ($ad->fieldvalues as $value) {
        $field = $value->field;

        if ($field && $field->type === 'file' && $value->value) {
            Storage::disk('public')->delete($value->value);
        }

        $value->delete();
    }

    $ad->delete();

    return ResponseHelper::success('Ad deleted successfully');
}



}
