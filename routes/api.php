<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\login_controller;
use App\Http\Controllers\admin\admin_controller;
use App\Http\Controllers\admin\user_controller;
use App\Http\Controllers\admin\country_controller;
use App\Http\Controllers\admin\category_controller;
use App\Http\Controllers\admin\company_controller;
use App\Http\Controllers\admin\allcategory_controller;
use App\Http\Controllers\admin\customfield_controller;
use App\Http\Controllers\admin\jobopportunity_controller;
use App\Http\Controllers\admin\ads_controller;
use App\Http\Controllers\admin\auction_controller;
use App\Http\Controllers\admin\slider_controller;
use App\Http\Controllers\admin\banner_controller;
use App\Http\Controllers\admin\admin_notification;

use App\Http\Controllers\user\user_notification;
use App\Http\Controllers\user\homepage_controller;
use App\Http\Controllers\user\user_opportunity_controller;
use App\Http\Controllers\user\user_auction_controller;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
 Route::group([
    'prefix' => 'all'
], function ($router) {
    Route::post('/login', [login_controller::class, 'login']);

});



Route::group([
    'middleware' => 'role:super_admin',
    'prefix' => 'admin'
], function ($router) {//view_admin,update_profile,profile,logout,view_admin,
    Route::post('/admins', [admin_controller::class, 'admins']);
    Route::post('/view_admin', [admin_controller::class, 'view_admin']);
    Route::post('/add_admin', [admin_controller::class, 'add_admin']);
    Route::post('/update_admin', [admin_controller::class, 'update_admin']);
    Route::post('/delete_admin', [admin_controller::class, 'delete_admin']);

    Route::post('/sendTestEmail', [admin_controller::class, 'sendTestEmail']);
});

Route::group([
    // 'middleware' => 'role',
    'prefix' => 'admin'
], function ($router) {//view_admin,update_profile,profile,logout,view_admin,
    Route::post('/update_profile', [admin_controller::class, 'update_profile']);
    Route::post('/profile', [admin_controller::class, 'profile']);
    Route::post('/logout', [admin_controller::class, 'logout']);

    Route::post('/countries', [country_controller::class, 'countries']);
    Route::post('/countries_state', [country_controller::class, 'countries_state']);
    Route::post('/add_country', [country_controller::class, 'add_country']);
    Route::post('/view_country', [country_controller::class, 'view_country']);
    Route::post('/update_country', [country_controller::class, 'update_country']);
    Route::post('/delete_country', [country_controller::class, 'delete_country']);
    Route::post('/country_regions', [country_controller::class, 'country_regions']);

    Route::post('/regions', [country_controller::class, 'regions']);
    Route::post('/add_region', [country_controller::class, 'add_region']);
    Route::post('/view_region', [country_controller::class, 'view_region']);
    Route::post('/update_region', [country_controller::class, 'update_region']);
    Route::post('/delete_region', [country_controller::class, 'delete_region']);


    Route::post('/categories', [category_controller::class, 'categories']);
    Route::post('/categories_state', [category_controller::class, 'categories_state']);
    Route::post('/add_category', [category_controller::class, 'add_category']);
    Route::post('/view_category', [category_controller::class, 'view_category']);
    Route::post('/update_category', [category_controller::class, 'update_category']);
    Route::post('/delete_category', [category_controller::class, 'delete_category']);
    Route::post('/category_sub', [category_controller::class, 'category_sub']);

    Route::post('/sub_categories', [category_controller::class, 'sub_categories']);
    Route::post('/add_subcategory', [category_controller::class, 'add_subcategory']);
    Route::post('/view_subcategory', [category_controller::class, 'view_subcategory']);
    Route::post('/update_subcategory', [category_controller::class, 'update_subcategory']);
    Route::post('/delete_subcategory', [category_controller::class, 'delete_subcategory']);

    Route::post('/companies', [company_controller::class, 'companies']);
    Route::post('/add_company', [company_controller::class, 'add_company']);
    Route::post('/view_company', [company_controller::class, 'view_company']);
    Route::post('/update_company', [company_controller::class, 'update_company']);
    Route::post('/delete_company', [company_controller::class, 'delete_company']);



    Route::post('/categoryTree', [allcategory_controller::class, 'categoryTree']);
    Route::post('/orderdcategoryTree', [allcategory_controller::class, 'orderdcategoryTree']);
    Route::post('/add_categoryTree', [allcategory_controller::class, 'add_categoryTree']);
    Route::post('/view_categoryTree', [allcategory_controller::class, 'view_categoryTree']);
    Route::post('/update_categoryTree', [allcategory_controller::class, 'update_categoryTree']);
    Route::post('/delete_categoryTree', [allcategory_controller::class, 'delete_categoryTree']);
    Route::post('/reorder_categoryTree', [allcategory_controller::class, 'reorder_categoryTree']);

    Route::post('/custom_fields', [customfield_controller::class, 'custom_fields']);
    Route::post('/add_field', [customfield_controller::class, 'add_field']);
    Route::post('/view_field/{field}', [customfield_controller::class, 'view_field']);
    Route::post('/update_field/{field}', [customfield_controller::class, 'update_field']);
    Route::post('/delete_field/{field}', [customfield_controller::class, 'delete_field']);
    Route::post('/category_fields/{category_id}', [customfield_controller::class, 'category_fields']);
    Route::post('/addupdate_fieldvalue', [customfield_controller::class, 'addupdate_fieldvalue']);


    Route::post('/opportunities', [jobopportunity_controller::class, 'opportunities']);
    Route::post('/add_opportunity', [jobopportunity_controller::class, 'add_opportunity']);
    Route::post('/update_opportunity/{id}', [jobopportunity_controller::class, 'update_opportunity']);
    Route::post('/view_opportunity/{id}', [jobopportunity_controller::class, 'view_opportunity']);
    Route::post('/delete_opportunity/{id}', [jobopportunity_controller::class, 'delete_opportunity']);
    Route::post('/company_opportunities/{id}', [jobopportunity_controller::class, 'company_opportunities']);
    Route::post('/user_opportunities/{id}', [jobopportunity_controller::class, 'user_opportunities']);

    Route::post('/country_opportunities/{id}', [jobopportunity_controller::class, 'country_opportunities']);
    Route::post('/category_opportunities/{id}', [jobopportunity_controller::class, 'category_opportunities']);
    Route::post('/region_opportunities/{id}', [jobopportunity_controller::class, 'region_opportunities']);


    Route::post('/users', [user_controller::class, 'users']);
    Route::post('/add_user', [user_controller::class, 'add_user']);
    Route::post('/view_user', [user_controller::class, 'view_user']);
    Route::post('/update_user', [user_controller::class, 'update_user']);
    Route::post('/delete_user', [user_controller::class, 'delete_user']);
    Route::post('/filter_users', [user_controller::class, 'filter_users']);

    Route::post('/all_ads', [ads_controller::class, 'all_ads']);
    Route::post('/add_ad', [ads_controller::class, 'add_ad']);
    Route::post('/view_ad', [ads_controller::class, 'view_ad']);
    Route::post('/update_ad', [ads_controller::class, 'update_ad']);
    Route::post('/delete_ad', [ads_controller::class, 'delete_ad']);
    Route::post('/filter_ads', [ads_controller::class, 'filter_ads']);

    Route::post('/auctions', [auction_controller::class, 'auctions']);
    Route::post('/add_auction', [auction_controller::class, 'add_auction']);
    Route::post('/view_auction/{id}', [auction_controller::class, 'view_auction']);
    Route::post('/update_auction/{id}', [auction_controller::class, 'update_auction']);
    Route::post('/update_status/{id}', [auction_controller::class, 'update_status']);
    Route::post('/delete_auction/{id}', [auction_controller::class, 'delete_auction']);
    Route::post('/delete_image/{id}', [auction_controller::class, 'delete_image']);
    Route::post('/filter_auction', [auction_controller::class, 'filter_auctions']);

    Route::get('/sliders', [slider_controller::class, 'sliders']);
    Route::post('/add_slider', [slider_controller::class, 'add_slider']);
    Route::get('/view_slider/{id}', [slider_controller::class, 'view_slider']);
    Route::post('/update_slider/{id}', [slider_controller::class, 'update_slider']);
    Route::get('/delete_slider/{id}', [slider_controller::class, 'delete_slider']);


    Route::get('/banners', [banner_controller::class, 'banners']);
    Route::post('/add_banner', [banner_controller::class, 'add_banner']);
    Route::get('/view_banner/{id}', [banner_controller::class, 'view_banner']);
    Route::post('/update_banner/{id}', [banner_controller::class, 'update_banner']);
    Route::get('/delete_banner/{id}', [banner_controller::class, 'delete_banner']);

    Route::post('/send_notification', [admin_notification::class, 'send_notification']);



});




Route::group([
    'prefix' => 'user'
], function ($router) {//view_admin,update_profile,profile,logout,view_admin,
    Route::post('/saveFcmToken', [user_notification::class, 'saveFcmToken']);

    Route::get('/my_opportunities', [user_opportunity_controller::class, 'my_opportunities']);
    Route::get('/my_opportunities/{id}', [user_opportunity_controller::class, 'my_opportunity']);
    Route::post('/add_opportunity', [user_opportunity_controller::class, 'add_opportunity']);
    Route::post('/update_opportunity/{id}', [user_opportunity_controller::class, 'update_opportunity']);
    Route::get('/delete_opportunity/{id}', [user_opportunity_controller::class, 'delete_opportunity']);


    Route::get('/my_auctions', [user_auction_controller::class, 'my_auctions']);
    Route::get('/my_auctions/{id}', [user_auction_controller::class, 'my_auction']);
    Route::post('/add_auction', [user_auction_controller::class, 'add_auction']);
    Route::post('/update_auction/{id}', [user_auction_controller::class, 'update_auction']);
    Route::get('/delete_auction/{id}', [user_auction_controller::class, 'delete_auction']);
    Route::get('/delete_image/{id}', [user_auction_controller::class, 'delete_image']);


});



Route::group([
    'prefix' => 'Home'
], function ($router) {
    Route::get('/countries', [homepage_controller::class, 'countries']);
    Route::get('countryRegions/{id}',[homepage_controller::class, 'countryRegions']);
    Route::get('parentCategories',[homepage_controller::class, 'parentCategories']);

    Route::get('/ads', [homepage_controller::class, 'all_ads']);
    Route::get('/ads/{id}', [homepage_controller::class, 'view_ad']);

    Route::get('/opportunities', [homepage_controller::class, 'opportunities']);
    Route::get('/opportunities/{id}', [homepage_controller::class, 'view_opportunity']);
    Route::get('/auction', [homepage_controller::class, 'the_auction']);
    Route::get('/auction/{id}', [homepage_controller::class, 'view_auction']);

    Route::get('/sliders', [homepage_controller::class, 'sliders']);
    Route::get('/banners', [homepage_controller::class, 'banners']);

});



