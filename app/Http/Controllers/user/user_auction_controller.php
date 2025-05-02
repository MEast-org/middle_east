<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\auction;
use App\Models\auction_images;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class user_auction_controller extends Controller
{
    public function __construct()
    {
        $this->middleware('user_company_auth');
    }

    public function my_auctions()
    {
        $user = auth()->user();
        if (!$user) return ResponseHelper::error('Unauthorized', null, 401);

        $auctions = $user->auctions()->with(['publisher', 'images', 'category.ancestors', 'country', 'region'])->latest()->paginate(request('page_size', 10));

        return ResponseHelper::success('My auctions', $auctions);
    }

    public function my_auction($id)
    {
        $user = auth()->user();
        $auction = auction::with(['publisher', 'images', 'category.ancestors', 'country', 'region'])->find($id);
        if (!$auction) return ResponseHelper::error('Not found', null, 404);

        if (!$user || !($auction->publisher_id == $user->id && $auction->publisher_type == ($user instanceof \App\Models\company ? 'company' : 'user')))
            return ResponseHelper::error('Forbidden', null, 403);

        return ResponseHelper::success('Auction details', $auction);
    }

    public function add_auction(Request $request)
    {
        $user = auth()->user();
        if (!$user) return ResponseHelper::error('Unauthorized', null, 401);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'region_id' => 'nullable|exists:regions,id',
            'country_id' => 'nullable|exists:countries,id',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'images' => 'required|array',
            'images.*' => 'required|image|max:2048',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|string',
        ]);

        $validated['publisher_type'] = $user instanceof \App\Models\company ? 'company' : 'user';
        $validated['publisher_id'] = $user->id;


        DB::beginTransaction();
        try {
        $auction = auction::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('auctions', 'public');
                auction_images::create([
                    'auction_id' => $auction->id,
                    'image_path' => $path,
                ]);
            }
        }
        DB::commit();
        return ResponseHelper::success('Auction created', $auction->load('images'));
    } catch (\Exception $e) {
        DB::rollBack();
        return ResponseHelper::error('Failed to create auction',$e->getMessage() , 500);
    }

    }

    public function update_auction(Request $request, $id)
    {
        $user = auth()->user();
        $auction = auction::find($id);
        if (!$auction) return ResponseHelper::error('Not found', null, 404);

        if (!$user || !($auction->publisher_id == $user->id && $auction->publisher_type == (get_class($user) === 'App\\Models\\company' ? 'company' : 'user')))
            return ResponseHelper::error('Forbidden', null, 403);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'region_id' => 'sometimes|nullable|exists:regions,id',
            'country_id' => 'sometimes|nullable|exists:countries,id',
            'description' => 'sometimes|nullable|string',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'start_date' => 'sometimes|nullable|date|after_or_equal:today',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'images' => 'sometimes|array',
            'images.*' => 'sometimes|image|max:2048',
            'social_links' => 'sometimes|nullable|array',
            'social_links.*' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try{
        $auction->update($validated);

        if ($request->hasFile('images')) {
                 // رفع الصور الجديدة
            foreach ($request->file('images') as $image) {
                $path = $image->store('auctions', 'public');
                auction_images::create([
                    'auction_id' => $auction->id,
                    'image_path' => $path,
                ]);
            }
        }
    DB::commit();
          return ResponseHelper::success('Auction updated', $auction->load('images'));
        } catch (\Exception $e) {
        DB::rollBack();
        return ResponseHelper::error('Failed to update auction',$e->getMessage() , 500);

    }

        return ResponseHelper::success('Auction updated', $auction->load('images'));
    }

    public function delete_auction($id)
    {
        $user = auth()->user();
        $auction = auction::with('images')->find($id);
        if (!$auction) return ResponseHelper::error('Not found', null, 404);

        if (!$user || !($auction->publisher_id == $user->id && $auction->publisher_type == (get_class($user) === 'App\\Models\\company' ? 'company' : 'user')))
            return ResponseHelper::error('Forbidden', null, 403);

        foreach ($auction->images as $img) {
            Storage::disk('public')->delete($img->image_path);
            $img->delete();
        }

        $auction->delete();
        return ResponseHelper::success('Auction deleted');
    }

    public function delete_image($id)
    {
        $user = auth()->user();
        if (!$user) return ResponseHelper::error('Unauthorized', null, 401);

        $image = auction_images::with('auction')->find($id);
        if (!$image || !$image->auction) {
            return ResponseHelper::error('Image not found', null, 404);
        }

        $isCompany = get_class($user) === 'App\\Models\\company';
        if ($image->auction->publisher_id !== $user->id || $image->auction->publisher_type !== ($isCompany ? 'company' : 'user')) {
            return ResponseHelper::error('Forbidden', null, 403);
        }

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return ResponseHelper::success('Image deleted successfully.');
    }

}
