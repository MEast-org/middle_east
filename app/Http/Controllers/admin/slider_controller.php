<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\slider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;

class slider_controller extends Controller
{

       /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }


    public function sliders(Request $request)
    {
        $pageSize = $request->input('page_size', 10); // افتراضي 10 لو ما بعت
        $sliders = slider::latest()->paginate($pageSize);

        return ResponseHelper::success('Sliders retrieved successfully.', $sliders);
    }

    public function view_slider($id)
    {
        $slider = slider::find($id);

        if (!$slider) {
            return ResponseHelper::error('slider not found.');
        }

        return ResponseHelper::success('slider retrieved successfully.', $slider);
    }

    public function add_slider(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // صورة فقط
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
        ]);

        // رفع الصورة
        $imagePath = $request->file('image')->store('sliders', 'public');
        $validated['image'] = $imagePath;

        $slider = slider::create($validated);

        return ResponseHelper::success('slider created successfully.', $slider);
    }

    public function update_slider(Request $request, $id)
    {
        $slider = slider::find($id);

        if (!$slider) {
            return ResponseHelper::error('slider not found.');
        }

        $validated = $request->validate([
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
        ]);

        // إذا تم رفع صورة جديدة
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة
            if ($slider->image && Storage::disk('public')->exists($slider->image)) {
                Storage::disk('public')->delete($slider->image);
            }
            // رفع الجديدة
            $imagePath = $request->file('image')->store('sliders', 'public');
            $validated['image'] = $imagePath;
        }

        $slider->update($validated);

        return ResponseHelper::success('slider updated successfully.', $slider);
    }

    public function delete_slider($id)
    {
        $slider = slider::find($id);

        if (!$slider) {
            return ResponseHelper::error('slider not found.');
        }

        // حذف الصورة
        if ($slider->image && Storage::disk('public')->exists($slider->image)) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();

        return ResponseHelper::success('slider deleted successfully.');
    }

}
