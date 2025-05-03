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

}
