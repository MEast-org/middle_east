<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\job_opportunity;
use App\Models\company;
use App\Models\country;
use App\Models\category;
use App\Models\region;
use App\Models\User;
use App\Models\ads;
use App\Models\auction;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;

class statistics_controller extends Controller
{

       /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }

    public function statistics(Request $request)
    {

        $validated = $request->validate([
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'category_id' => 'nullable|exists:categories,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $filters = function ($query) use ($validated) {
            if (!empty($validated['country_id'])) {
                $query->where('country_id', $validated['country_id']);
            }

            if (!empty($validated['region_id'])) {
                $query->where('region_id', $validated['region_id']);
            }

            if (!empty($validated['category_id'])) {
                $query->where('category_id', $validated['category_id']);
            }

            if (!empty($validated['from_date'])) {
                $query->whereDate('created_at', '>=', $validated['from_date']);
            }

            if (!empty($validated['to_date'])) {
                $query->whereDate('created_at', '<=', $validated['to_date']);
            }
        };

        // يتم تطبيق الفئة فقط على الجداول التي تحتوي على category_id
        $adsCount =ads::where($filters)->count();
        $jobsCount =job_opportunity::where($filters)->count();
        $auctionsCount =auction::where($filters)->count();

        // للمستخدمين والشركات بدون فلترة category_id
        $userFilters = function ($query) use ($validated) {
            if (!empty($validated['country_id'])) {
                $query->where('country_id', $validated['country_id']);
            }

            if (!empty($validated['region_id'])) {
                $query->where('region_id', $validated['region_id']);
            }

            if (!empty($validated['from_date'])) {
                $query->whereDate('created_at', '>=', $validated['from_date']);
            }

            if (!empty($validated['to_date'])) {
                $query->whereDate('created_at', '<=', $validated['to_date']);
            }
        };

        $usersCount = User::where($userFilters)->count();
        $companiesCount = company::where($userFilters)->count();

        $data = [
            'ads' => $adsCount,
            'jobs' => $jobsCount,
            'auctions' => $auctionsCount,
            'users' => $usersCount,
            'companies' => $companiesCount,
        ];

        return ResponseHelper::success('Statistics retrieved successfully', $data);
    }

            public function AnalyticsCategory()
        {
            $parentCategories = category::whereNull('parent_id')->get();

            $result = $parentCategories->map(function ($parent) {
                // جلب جميع الأبناء بما فيهم هو نفسه
                $categoryIds = category::where('parent_id', $parent->id)
                                ->pluck('id')->push($parent->id);

                return [
                    'category_en' => $parent->en_name,
                    'category_ar' => $parent->ar_name,
                    'ads_count' => DB::table('ads')
                        ->whereIn('category_id', $categoryIds)
                        ->count(),
                    'auctions_count' => DB::table('auctions')
                        ->whereIn('category_id', $categoryIds)
                        ->count(),
                    'jobs_count' => DB::table('job_opportunities')
                        ->whereIn('category_id', $categoryIds)
                        ->count(),
                ];
            });

            return ResponseHelper::success('analyitics retrieved successfully', $result);



        }

}
