<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\category;
use Illuminate\Support\Facades\Storage;

class allcategory_controller extends Controller
{
       /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');

    }


    public function categoryTree()
    {
        $categories = Category::defaultOrder()->get()->toTree();
        return response()->json(['allCategories'=>$categories]);
    }

    public function orderdcategoryTree()
    {
            // الحصول على الشجرة كاملة مع الترتيب
            $tree = Category::query()
            ->orderBy('sort_order', 'asc') // الفرز أولاً
            ->get()
            ->toTree();

        return response()->json($tree);
    }

    // إضافة فئة جديدة
    public function add_categoryTree(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ar_name' => 'required|string|max:100',
            'en_name' => 'required|string|max:100',
            'icon' =>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'state' => 'sometimes|in:active,inactive',
            'sort_order' => 'sometimes|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $validator->validated();

        // معالجة الصورة للفئات الرئيسية فقط
        if (!$request->parent_id && $request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('icons', 'public');
        } else {
            unset($data['icon']);
        }

        // إنشاء الفئة
        if ($request->parent_id) {
            $parent = category::find($request->parent_id);
            $category = $parent->children()->create($data);
        } else {
            $category = category::create($data);
        }

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }


    public function view_categoryTree(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:categories,id',
        ]);

        $category = Category::findOrFail($request->id);

        $category = Category::with([
            'parent',
            'children' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }
        ])
        ->findOrFail($request->id);

        return response()->json(['category'=>$category]);
    }

    // تحديث فئة
    public function update_categoryTree(Request $request)
    {

        $category = Category::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:categories,id',
            'ar_name' => 'sometimes|required|string|max:100',
            'en_name' => 'sometimes|required|string|max:100',
            'icon' =>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_id' => 'sometimes|nullable|exists:categories,id|not_in:'.$request->id,
            'state' => 'sometimes|in:active,inactive',
            'sort_order' => 'sometimes|integer'
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $validator->validated();

        // معالجة الصورة للفئات الرئيسية فقط
        if ($category->isRoot() && $request->hasFile('icon')) {
            // حذف الصورة القديمة إذا وجدت
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $data['icon'] = $request->file('icon')->store('icons', 'public');
        } elseif (!$category->isRoot()) {
            unset($data['icon']);
        }

        // تغيير الوالد إذا طُلب
        if ($request->has('parent_id') && $request->parent_id != $category->parent_id) {
            if ($request->parent_id) {
                $newParent = Category::find($request->parent_id);
                $category->appendToNode($newParent)->save();
            } else {
                $category->makeRoot()->save();
            }
        }

        $category->update($data);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    // حذف فئة
    public function delete_categoryTree(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:categories,id',
        ]);
        $category = Category::findOrFail($request->id);

        // حذف الصورة إذا كانت فئة رئيسية
        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }


    public function reorder_categoryTree(Request $request)
{
    $request->validate([
        'id' => 'required|exists:categories,id',
        'new_order' => 'required|integer|min:1' // تغيير الحد الأدنى إلى 1 بدلاً من 0
    ]);

    $category = Category::findOrFail($request->id);
    $parentId = $category->parent_id;

    // الحصول على الأشقاء في نفس المستوى مع الترتيب الحالي
    $siblings = Category::where('parent_id', $parentId)
                      ->where('id', '!=', $category->id)
                      ->orderBy('sort_order')
                      ->get();

    // التأكد من أن الترتيب الجديد ضمن الحدود الصحيحة
    $maxOrder = $siblings->max('sort_order') ?? 0;
    $newOrder = min($request->new_order, $maxOrder + 1);

    // تحديث ترتيب الفئة المحددة
    $category->sort_order = $newOrder;
    $category->save();

    // إعادة ترتيب الأشقاء
    $currentOrder = 1;
    foreach ($siblings as $sibling) {
        if ($currentOrder == $newOrder) {
            $currentOrder++; // تخطي الترتيب الجديد للفئة المحددة
        }
        $sibling->sort_order = $currentOrder;
        $sibling->save();
        $currentOrder++;
    }

    return response()->json([
        'success' => true,
        'message' => 'تم إعادة الترتيب بنجاح',
        'data' => [
            'category_id' => $category->id,
            'new_order' => $newOrder
        ]
    ]);
}


}
