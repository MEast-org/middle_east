<?php

namespace App\Http\Controllers\user;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\country;
use App\Models\category;
use App\Models\region;
use App\Models\policy_terms;

use App\Models\slider;
use App\Models\banner;

use App\Models\ads;
use App\Models\job_opportunity;
use App\Models\auction;
use App\Models\contact_info;


class homepage_controller extends Controller
{

    public function countries()
{
    $countries = country::active()->select('id', 'en_name','ar_name')->get(); // بدون with('regions')
    return ResponseHelper::success('all country', $countries);
}
public function countryRegions($id)
{
    $country = country::find($id);

    if (!$country) {
        return ResponseHelper::error('not exist ', null, 404);
    }

    $regions = $country->regions()->select('id', 'en_name','ar_name')->get();

    return ResponseHelper::success('the regions for this country', $regions);
}

public function parentCategories()
{
    $categories = category::active()->whereIsRoot()->get(); // from nestedset package
    return ResponseHelper::success('the category parents ', $categories);
}

public function categoryTree()
{
    $categories = category::active()->select('id', 'en_name','ar_name','icon')->defaultOrder()->get()->toTree();
    return ResponseHelper::success('categoryTree', $categories);
}

public function contacts()
    {
        $infos = contact_info::latest()->get(['id', 'platform', 'link']);
        return ResponseHelper::success('Contact info list', $infos);
    }

       // عرض الكل
       public function policyTerms()
       {
           $all = policy_terms::all();
           return ResponseHelper::success('Pages retrieved successfully',$all);
       }

 // عرض صفحة معينة
    public function view_policyTerms($key, $locale)
    {
        $page = policy_terms::where('key', $key)->where('locale', $locale)->first();

        if (!$page) {
            return ResponseHelper::error('Page not found.', 404);
        }

        return ResponseHelper::success('Page retrieved successfully',$page);
    }

public function all_ads(Request $request)
{
    $query = ads::with([
        'publisher',
        'fieldvalues.field'
    ]);

    if ($request->filled('country_id')) {
        $query->where('country_id', $request->country_id);
    }

    if ($request->filled('region_id')) {
        $query->where('region_id', $request->region_id);
    }

    if ($request->filled('category_id')) {
        $category = category::find($request->category_id);
        if ($category) {
            $categoryIds = category::descendantsAndSelf($request->category_id)->pluck('id')->toArray();
            $query->whereIn('category_id', $categoryIds);
        }
    }

    // ترتيب حسب المشاهدات أو الأحدث
    if ($request->boolean('views')) {
        $query->orderByDesc('views');
    } else {
        $query->latest();
    }

    $ads = $query->active()->paginate($request->get('page_size', 10));

    return ResponseHelper::success('Ads retrieved successfully', $ads);
}

public function view_ad($id)
{
    $ad = ads::with(['publisher','country', 'region', 'category.ancestors', 'fieldvalues.field'])
        ->find($id);

    if (!$ad) {
        return ResponseHelper::error('Ad not found', null, 404);
    }
    $ad->incrementViews();


    return ResponseHelper::success('Ad retrieved successfully', $ad);
}





public function opportunities(Request $request)
{
    $query = job_opportunity::with([
        'publisher',
    ]);

    if ($request->filled('country_id')) {
        $query->where('country_id', $request->country_id);
    }

    if ($request->filled('region_id')) {
        $query->where('region_id', $request->region_id);
    }

    if ($request->filled('category_id')) {
        $category = category::find($request->category_id);
        if ($category) {
            $categoryIds =category::descendantsAndSelf($request->category_id)->pluck('id')->toArray();
            $query->whereIn('category_id', $categoryIds);
        }
    }

    $opportunities = $query->active()->latest()->paginate($request->get('page_size',10));

    return ResponseHelper::success('Opportunities retrieved successfully', $opportunities);
}



public function view_opportunity($id)
{
    $opportunity = job_opportunity::with(['publisher', 'category.ancestors', 'country', 'region'])
        ->find($id);

    if (!$opportunity) {
        return ResponseHelper::error('Opportunity not found', null, 404);
    }
    $opportunity->incrementViews();

    return ResponseHelper::success('Opportunity retrieved successfully', $opportunity);
}


public function the_auction()
{
    $auction = auction::active()->with(['publisher', 'images', 'category.ancestors', 'country', 'region'])->latest()->first();
    if (!$auction) {
        return ResponseHelper::error('not found an active auction', null, 404);

    }


    return ResponseHelper::success('auction retrieved successfully', $auction);

}

public function view_auction($id)
{
    $auction = auction::with(['publisher', 'images', 'category.ancestors', 'country', 'region'])
                       ->find($id);

    if (!$auction) {
        return ResponseHelper::error('Opportunity not found', null, 404);

    }
    return ResponseHelper::success('auction retrieved successfully', $auction);

}

public function sliders()
{
    $sliders = slider::latest()
        ->get()
        ->makeHidden(['created_at', 'updated_at']);

    return ResponseHelper::success('Sliders retrieved successfully', $sliders);
}

public function banners()
{
    $banners = banner::latest()
        ->get()
        ->makeHidden(['created_at', 'updated_at']);

    return ResponseHelper::success('Banners retrieved successfully', $banners);
}







}
