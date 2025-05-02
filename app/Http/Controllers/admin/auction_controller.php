<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\auction;
use App\Models\auction_images;
use App\Models\company;
use App\Models\user;
use App\Models\country;
use App\Models\region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Validator;

class auction_controller extends Controller
{
        /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }

    public function auctions()
    {
        $auctions = auction::with(['publisher', 'images', 'category.ancestors', 'country', 'region'])->latest()->paginate(10);
        return response()->json(['auction' => $auctions]);
    }


    public function view_auction($id)
    {
        $auction = auction::with(['publisher', 'images', 'category.ancestors', 'country', 'region'])->find($id);
        if (!$auction) {
            return response()->json(['error' => 'Auction not found'], 404);
        }
        return response()->json(['auction' => $auction]);
    }


    public function add_auction(Request $request)
{
    $validated = $request->validate([
        'publisher_type' => 'required|string|in:user,company',
        'publisher_id' => [
            'required',
            function ($attribute, $value, $fail) use ($request) {
                $table = $request->publisher_type === 'user' ? 'users' : 'companies';
                if (!DB::table($table)->where('id', $value)->exists()) {
                    $fail("The selected $attribute is invalid.");
                }
            }
        ],

        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'region_id' => 'required|exists:regions,id',
        'country_id' => 'required|exists:countries,id',
        'description' => 'nullable|string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',

       'images' => 'required|array',
        'images.*' => 'required|image|max:2048',
        'social_links' => 'nullable|array',
        'social_links.*' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        $auction = auction::create([
            'publisher_type' => $validated['publisher_type'],
            'publisher_id' => $validated['publisher_id'],
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'region_id' => $validated['region_id'],
            'country_id' => $validated['country_id'],
            'description' => $validated['description'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' =>$validated['end_date'],
            'social_links'=> $validated['social_links'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('auctions', 'public');
                $auction->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        DB::commit();
        return response()->json(['message' => 'Auction created successfully', 'auction' => $auction], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Failed to create auction', 'details' => $e->getMessage()], 500);
    }
}

public function update_auction(Request $request, $id)
{
    $auction = auction::find($id);
    if (!$auction) {
        return response()->json(['error' => 'Auction not found'], 404);
    }
    $validator = Validator::make($request->all(), [
        'publisher_type' => 'sometimes|required|string|in:user,company',
        'publisher_id' => [
            'sometimes', 'required', 'integer',
            function ($attribute, $value, $fail) use ($request) {
                if (!$request->has('publisher_type')) {
                    return $fail('The publisher_type field is required when publisher_id is present.');
                }

                $type = $request->publisher_type;
                $table = $type === 'user' ? 'users' : 'companies';

                if (!DB::table($table)->where('id', $value)->exists()) {
                    $fail("The selected $attribute is invalid for the given publisher_type ($type).");
                }
            }
        ],

        'name' => 'sometimes|required|string|max:255',
        'category_id' => 'sometimes|required|exists:categories,id',
        'region_id' => 'sometimes|required|exists:regions,id',
        'country_id' => 'sometimes|required|exists:countries,id',

        'description' => 'sometimes|nullable|string',
        'latitude' => 'sometimes|nullable|numeric',
        'longitude' => 'sometimes|nullable|numeric',

        'start_date' => 'sometimes|required|date',
        'end_date' => 'sometimes|required|date|after_or_equal:start_date',
        'status' => 'sometimes|required|in:pending,active,completed,expired',
        'images' => 'sometimes|array',
        'images.*' => 'sometimes|image|max:2048',

        'social_links' => 'sometimes|nullable|array',
        'social_links.*' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $validated = $validator->validated();

    DB::beginTransaction();
    try{
    // تحديث بيانات المزاد
    $auction->update($validated);

    // إضافة الصور الجديدة إن وجدت
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('auctions', 'public');
            $auction->images()->create([
                'image_path' => $path,
            ]);
        }
    }
            DB::commit();
            return response()->json([
                'message' => 'Auction updated successfully.',
                'auction' => $auction->load('images'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create auction', 'details' => $e->getMessage()], 500);
        }
}



    public function delete_auction($id)
    {
        $auction = Auction::find($id);
        if (!$auction) {
            return response()->json(['error' => 'Auction not found'], 404);
        }
        foreach ($auction->images as $img) {
            Storage::disk('public')->delete($img->image_path);
            $img->delete();
        }

        $auction->delete();

        return response()->json(['message' => 'Auction deleted successfully']);
    }





public function filter_auctions(Request $request)
{
    $query = auction::query();

    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->filled('region_id')) {
        $query->where('region_id', $request->region_id);
    }

    if ($request->filled('country_id')) {
        $query->where('country_id', $request->country_id);
    }

    if ($request->filled('publisher_type')) {
        $query->where('publisher_type', $request->publisher_type);
    }

    if ($request->filled('publisher_id')) {
        $query->where('publisher_id', $request->publisher_id);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('start_date', '>=', Carbon::parse($request->start_date)->format('Y-m-d H:i:s'));
    }

    if ($request->filled('end_date')) {
        $query->whereDate('end_date', '<=', Carbon::parse($request->end_date)->format('Y-m-d H:i:s'));
    }

    $auctions = $query->with(['publisher', 'images','category.ancestors', 'country', 'region'])->latest()->paginate(10);

    return response()->json(['auctions' => $auctions]);
}


public function update_status(Request $request, $id)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,active,completed,expired',
    ]);

    $auction = auction::find($id);
    if (!$auction) {
        return response()->json(['error' => 'Auction not found'], 404);
    }

    $auction->status = $validated['status'];
    $auction->save();

    return response()->json(['message' => 'Auction status updated successfully', 'auction' => $auction], 200);
}




    public function delete_image($id)
    {
        $image = auction_images::find($id);

        if (!$image) {
            return response()->json(['error' => 'image not found'], 404);
        }

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return response()->json(['message' => 'Image deleted successfully.']);
    }
}


