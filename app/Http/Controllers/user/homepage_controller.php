<?php

namespace App\Http\Controllers\user;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\country;
use App\Models\category;
use App\Models\region;

use App\Models\ads;
use App\Models\job_opportunity;
use App\Models\auction;


class homepage_controller extends Controller
{

    public function countries()
{
    $countries = country::where('state', 'active')->select('id', 'en_name','ar_name')->get(); // بدون with('regions')
    return ResponseHelper::success('all country', $countries);
}
public function countryRegions($id)
{
    $country = Country::find($id);

    if (!$country) {
        return ResponseHelper::error('not exist ', null, 404);
    }

    $regions = $country->regions()->select('id', 'en_name','ar_name')->get();

    return ResponseHelper::success('the regions for this country', $regions);
}

public function parentCategories()
{
    $categories = category::where('state', 'active')->whereIsRoot()->get(); // from nestedset package
    return ResponseHelper::success('the category parents ', $categories);
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
            $categoryIds =category::descendantsAndSelf($request->category_id)->pluck('id')->toArray();
            $query->whereIn('category_id', $categoryIds);
        }
    }

    $ads = $query->latest()->paginate(10);

    return ResponseHelper::success('Ads retrieved successfully', $ads);
}

public function view_ad($id)
{
    $ad = ads::with(['country', 'region', 'category.ancestors', 'publisher', 'fieldvalues.field'])
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
        'company',
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
            $categoryIds =category::descendantsAndSelf($request->category_id)->pluck('id')->toArray();
            $query->whereIn('category_id', $categoryIds);
        }
    }

    $opportunities = $query->latest()->paginate(10);

    return ResponseHelper::success('Opportunities retrieved successfully', $opportunities);
}

public function view_opportunity($id)
{
    $opportunity = job_opportunity::with(['company', 'category.ancestors', 'country', 'region', 'fieldvalues.field'])
        ->find($id);

    if (!$opportunity) {
        return ResponseHelper::error('Opportunity not found', null, 404);
    }
    $opportunity->incrementViews();

    return ResponseHelper::success('Opportunity retrieved successfully', $opportunity);
}


public function the_auction()
{
    $auction = auction::where('status', 'active')->latest()->first();
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







}
