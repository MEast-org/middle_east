<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\opportunityRequest;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\job_opportunity;
use App\Models\company;
use App\Models\country;
use App\Models\category;
use App\Models\region;

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
            $opportunities = job_opportunity::with(['company', 'category', 'country', 'region','fieldvalues.field'])
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $opportunities
            ],201);

    }

    public function view_opportunity($id)
    {

            $opportunity = job_opportunity::with(['company', 'category', 'country', 'region','fieldvalues.field'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $opportunity
            ],201);

    }

      // إنشاء فرصة جديدة
      public function add_opportunity(opportunityRequest $request)
      {
              $opportunity = job_opportunity::create($request->validated());

              return response()->json([
                  'success' => true,
                  'data' => $opportunity,
                  'message' => 'Job opportunity created successfully'
              ], 201);
      }

      public function update_opportunity(opportunityRequest $request, $id)
    {

            $opportunity = job_opportunity::findOrFail($id);
            $opportunity->update($request->validated());

            return response()->json([
                'success' => true,
                'data' => $opportunity,
                'message' => 'Job opportunity updated successfully'
            ],201);
    }

    // حذف فرصة
    public function delelet_opportunity($id)
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
                        ->with(['category', 'country', 'region'])
                        ->paginate(10);

                    return response()->json([
                        'success' => true,
                        'company' => $company->only(['id', 'en_name','ar_name']),
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
                        ->with(['category', 'company', 'region'])
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
                        ->with(['country', 'company', 'category'])
                        ->paginate(10);

                    return response()->json([
                        'success' => true,
                        'region' => $region->only(['id', 'en_name','ar_name']),
                        'opportunities' => $opportunities
                    ]);
            }

}
