<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\job_category;
use App\Models\sub_category;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;

class category_controller extends Controller
{
      /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');

    }

    public function categories()
    {
        $categories = job_category::with('subcategories')->get();
        return response()->json(['categories' => $categories]);
    }

    /**
     * عرض التصنيفات حسب الحالة
     */
    public function categories_state()
    {
        $validator = Validator::make(request()->all(), [
            'state' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $categories = job_category::where('state', request()->state)->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    /**
     * إضافة تصنيف جديد
     */
    public function add_category(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'en_name' => 'required|string|max:100',
            'ar_name' => 'required|string|max:100',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $category = job_category::create($validator->validated());

        if ($request->hasFile('icon')) {
            $category->icon = $request->file('icon')->store('icons', 'public');
            $category->save();
        }

        return response()->json([
            'message' => 'Category successfully created',
            'category' => $category
        ], 201);
    }

    /**
     * عرض تصنيف معين
     */
    public function view_category(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:job_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $category = job_category::find($request->id);

        return response()->json([
            'category' => $category,
        ]);
    }

    /**
     * تحديث بيانات التصنيف
     */
    public function update_category(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:job_categories,id',
            'en_name' => 'sometimes|required|string|max:100',
            'ar_name' => 'sometimes|required|string|max:100',
            'icon' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'state' => 'sometimes|required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $category = job_category::find($request->id);

       // التعامل مع الصورة
     if ($request->has('icon')) {
        // إذا تم إرسال حقل icon كقيمة null أو فارغة
        if (is_null($request->icon) || $request->icon === '') {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $category->icon = null;
        }
        // إذا تم رفع ملف جديد
        elseif ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $category->icon = $request->file('icon')->store('icons', 'public');
        }
    }

        $category->update(array_merge(
            $validator->validated(),
            ['icon' => $category->icon]
        ));

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ], 200);
    }

    /**
     * حذف التصنيف
     */
    public function delete_category(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:job_categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $category = job_category::find($request->id);

        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * عرض التصنيفات الفرعية لتصنيف معين
     */
    public function category_sub(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:job_categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subCategories = job_category::find($request->id)->subcategories()->get();

        return response()->json([
            'success' => true,
            'sub_categories' => $subCategories
        ]);
    }

    ////////////////////////////manage subcategories//////////////////////



    public function sub_categories()
    {
        $subCategories = sub_category::with('category')->get();
        return response()->json(['sub_categories' => $subCategories]);
    }

    /**
     * إضافة تصنيف فرعي جديد
     */
    public function add_subcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:job_categories,id',
            'en_name' => 'required|string|max:100',
            'ar_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subCategory = sub_category::create([
            'category_id' => $request->category_id,
            'en_name' => $request->en_name,
            'ar_name' => $request->ar_name
        ]);

        return response()->json([
            'message' => 'Sub Category successfully created',
            'sub_category' => $subCategory
        ], 201);
    }

    /**
     * عرض تصنيف فرعي معين
     */
    public function view_subcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sub_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subCategory = sub_category::with('category')->find($request->id);

        return response()->json([
            'sub_category' => $subCategory,
        ]);
    }

    /**
     * تحديث بيانات التصنيف الفرعي
     */
    public function update_subcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sub_categories,id',
            'en_name' => 'sometimes|required|string|max:100',
            'ar_name' => 'sometimes|required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subCategory = sub_category::find($request->id);
        $subCategory->update($validator->validated());

        return response()->json([
            'message' => 'Sub Category updated successfully',
            'sub_category' => $subCategory
        ], 200);
    }

    /**
     * حذف تصنيف فرعي
     */
    public function delete_subcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sub_categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        sub_category::find($request->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sub Category deleted successfully'
        ]);
    }
}
