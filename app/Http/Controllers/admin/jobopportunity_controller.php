<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\job_opportunity;
use App\Models\company;
use App\Models\country;
use App\Models\category;
use App\Models\region;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class jobopportunity_controller extends Controller
{
      /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }

    public function opportunities(Request $request)
    {
            $perPage = $request->input('per_page', 10);
            $opportunities = job_opportunity::with(['publisher', 'category.ancestors', 'country', 'region'])
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $opportunities
            ],201);

    }

    public function view_opportunity($id)
    {

            $opportunity = job_opportunity::with(['publisher', 'category.ancestors', 'country', 'region'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $opportunity
            ],201);

    }



public function add_opportunity(Request $request)
{
    // التحقق من البيانات
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'country_id' => 'nullable|exists:countries,id',
        'region_id' => 'nullable|exists:regions,id',
        'description' => 'nullable|string',
        'starts_at' => 'nullable|date|after_or_equal:today',
        'expires_at' => 'nullable|date|after_or_equal:starts_at',
        'type' => 'required|in:full_time,part_time,contract,internship,remote',

        'min_salary' => 'nullable|numeric|min:0',
        'max_salary' => 'nullable|numeric|min:0|gte:min_salary',

        'social_links' => 'nullable|array',
        'social_links.*' => 'nullable|string',

        'publisher_type' => 'required|in:user,company',
        'publisher_id' => [
            'required',
            function ($attribute, $value, $fail) use ($request) {
                $table = $request->publisher_type === 'user' ? 'users' : 'companies';
                if (!DB::table($table)->where('id', $value)->exists()) {
                    $fail("The selected $attribute is invalid.");
                }
            },
        ],
    ]);



    // إنشاء فرصة العمل
    $opportunity = job_opportunity::create($validated);

    return response()->json([
        'success' => true,
        'data' => $opportunity,
        'message' => 'Job opportunity created successfully',
    ], 201);
}


public function update_opportunity(Request $request, $id)
{
    // إيجاد فرصة العمل
    $opportunity = job_opportunity::find($id);
    if (!$opportunity){
        return response()->json(['error' => 'Not found opportunity'], 404);
    };

    // التحقق من البيانات
    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'category_id' => 'sometimes|required|exists:categories,id',
        'country_id' => 'sometimes|nullable|exists:countries,id',
        'region_id' => 'sometimes|nullable|exists:regions,id',
        'description' => 'sometimes|nullable|string',
        'starts_at' => 'sometimes|nullable|date|after_or_equal:today',
        'expires_at' => 'sometimes|nullable|date|after_or_equal:starts_at',
        'type' => 'sometimes|required|in:full_time,part_time,contract,internship,remote',

        'min_salary' => 'sometimes|nullable|numeric|min:0',
        'max_salary' => 'sometimes|nullable|numeric|min:0|gte:min_salary',

        'social_links' => 'sometimes|nullable|array',
        'social_links.*' => 'nullable|string',
        'state' => 'sometimes|required|in:inactive,active',

        'publisher_type' => 'sometimes|required|in:user,company',
        'publisher_id' => [
            'sometimes',
            'required',
            function ($attribute, $value, $fail) use ($request) {
                $table = $request->publisher_type === 'user' ? 'users' : 'companies';
                if (!DB::table($table)->where('id', $value)->exists()) {
                    $fail("The selected $attribute is invalid.");
                }
            },
        ],
    ]);


    // تحديث البيانات
    $opportunity->update($validated);

    return response()->json([
        'success' => true,
        'data' => $opportunity,
        'message' => 'Job opportunity updated successfully',
    ]);
}


    // حذف فرصة
    public function delete_opportunity($id)
    {

        job_opportunity::findOrFail($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job opportunity deleted successfully'
            ],201);

    }

            public function company_opportunities($id)
            {

                if (!company::where('id', $id)->exists()){
                    return response()->json(['error' => 'Not found company'], 404);
                };
                    $company = company::findOrFail($id);


                    $opportunities = $company->opportunities()
                        ->with(['category.ancestors', 'country', 'region'])
                        ->latest()
                        ->paginate(10);

                    return response()->json([
                        'success' => true,
                        'company' => $company->only(['id','name']),
                        'opportunities' => $opportunities
                    ]);
            }


            public function user_opportunities($id)
            {

                if (!User::where('id', $id)->exists()){
                    return response()->json(['error' => 'Not found user'], 404);
                };
                    $user = User::findOrFail($id);


                    $opportunities = $user->opportunities()
                        ->with(['category.ancestors', 'country', 'region'])
                        ->latest()
                        ->paginate(10);

                    return response()->json([
                        'success' => true,
                        'user' => $user->only(['id','name']),
                        'opportunities' => $opportunities
                    ]);
            }

            public function country_opportunities($id)
            {
                if (!country::where('id', $id)->exists()){
                    return response()->json(['error' => 'Not found country'], 404);
                };

                    $country = country::findOrFail($id);


                    $opportunities = $country->opportunities()
                        ->with(['category.ancestors', 'company', 'region'])
                        ->latest()
                        ->paginate(10);

                    return response()->json([
                        'success' => true,
                        'country' => $country->only(['id', 'en_name','ar_name']),
                        'opportunities' => $opportunities
                    ]);
            }

            public function category_opportunities($id)
            {
                if (!category::where('id', $id)->exists()){
                    return response()->json(['error' => 'Not found category'], 404);
                };

                    $category = category::findOrFail($id);


                    $opportunities = $category->opportunities()
                        ->with(['country', 'company', 'region'])
                        ->latest()
                        ->paginate(10);

                    return response()->json([
                        'success' => true,
                        'category' => $category->only(['id', 'en_name','ar_name']),
                        'opportunities' => $opportunities
                    ]);
            }

            public function region_opportunities($id)
            {
                if (!region::where('id', $id)->exists()){
                    return response()->json(['error' => 'Not found region'], 404);
                };

                    $region = region::findOrFail($id);


                    $opportunities = $region->opportunities()
                        ->with(['country', 'company', 'category.ancestors'])
                        ->paginate(10);

                    return response()->json([
                        'success' => true,
                        'region' => $region->only(['id', 'en_name','ar_name']),
                        'opportunities' => $opportunities
                    ]);
            }

}
