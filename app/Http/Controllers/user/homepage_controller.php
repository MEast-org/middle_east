<?php

namespace App\Http\Controllers\user;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\country;
use App\Models\category;
use App\Models\region;

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



}
