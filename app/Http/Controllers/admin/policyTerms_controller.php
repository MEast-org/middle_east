<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\policy_terms;
use App\Helpers\ResponseHelper;

class policyTerms_controller extends Controller
{

  /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }

    // عرض الكل
    public function index()
    {
        $all = policy_terms::all();
        return ResponseHelper::success('Pages retrieved successfully',$all);
    }

    // عرض صفحة معينة
    public function show($key, $locale)
    {
        $page = policy_terms::where('key', $key)->where('locale', $locale)->first();

        if (!$page) {
            return ResponseHelper::error('Page not found.', 404);
        }

        return ResponseHelper::success('Page retrieved successfully',$page);
    }

    // إضافة
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'locale' => 'required|string|in:ar,en',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // تأكد من عدم التكرار
        if (policy_terms::where('key', $validated['key'])->where('locale', $validated['locale'])->exists()) {
            return ResponseHelper::error('Entry already exists.', 409);
        }

        $policyTerm = policy_terms::create($validated);

        return ResponseHelper::success('Page created successfully',$policyTerm);
    }

    // تعديل
    public function update(Request $request, $id)
    {
        $policyTerm = policy_terms::find($id);

        if (!$policyTerm) {
            return ResponseHelper::error('Page not found.', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $policyTerm->update($validated);

        return ResponseHelper::success( 'Page updated successfully',$policyTerm);
    }

    // حذف
    public function destroy($id)
    {
        $policyTerm = policy_terms::find($id);

        if (!$policyTerm) {
            return ResponseHelper::error('Page not found.', 404);
        }

        $policyTerm->delete();

        return ResponseHelper::success('Page deleted successfully', null);
    }
}

