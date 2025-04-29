<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\banner;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;

class banner_controller extends Controller
{

       /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }


    public function banners(Request $request)
    {
        $pageSize = $request->input('page_size', 10);
        $banners = banner::latest()->paginate($pageSize);

        return ResponseHelper::success('Banners retrieved successfully.', $banners);
    }

    public function view_banner($id)
    {
        $banner = banner::find($id);

        if (!$banner) {
            return ResponseHelper::error('banner not found.');
        }

        return ResponseHelper::success('banner retrieved successfully.', $banner);
    }

    public function add_banner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'nullable|string|max:255',
        ]);

        $banner = banner::create($validated);

        return ResponseHelper::success('banner created successfully.', $banner);
    }

    public function update_banner(Request $request, $id)
    {
        $banner = banner::find($id);

        if (!$banner) {
            return ResponseHelper::error('banner not found.');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'link' => 'nullable|string|max:255',
        ]);

        $banner->update($validated);

        return ResponseHelper::success('banner updated successfully.', $banner);
    }

    public function delete_banner($id)
    {
        $banner = banner::find($id);

        if (!$banner) {
            return ResponseHelper::error('banner not found.');
        }

        $banner->delete();

        return ResponseHelper::success('banner deleted successfully.');
    }



}
