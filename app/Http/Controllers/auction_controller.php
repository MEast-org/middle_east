<?php

namespace App\Http\Controllers;

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

class auction_controller extends Controller
{


    public function auctions()
    {
        $auctions = auction::with(['publisher', 'images', 'category', 'country', 'region'])->latest()->paginate(10);
        return response()->json($auctions);
    }


    public function view_auction($id)
    {
        $auction = auction::with(['publisher', 'images', 'category', 'country', 'region'])->findOrFail($id);
        return response()->json($auction);
    }


    public function add_auction(Request $request)
    {
        $request->validate([

            'publisher_type' => 'required|string|in:users,companies',
            'publisher_id' => 'required|exists:' . ($request->publisher_type == 'user' ? 'users' : 'companies') . ',id',

            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'region_id' => 'nullable|exists:regions,id',
            'country_id' => 'nullable|exists:countries,id',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',

            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'whatsapp' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $auction = auction::create([

                $validator->validated(),
                'status' => 'pending',
                'start_date' => Carbon::parse($validated['start_date'])->format('Y-m-d H:i:s'),
                'end_date' => Carbon::parse($validated['end_date'])->format('Y-m-d H:i:s'),

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


    public function delete_auction($id)
    {
        $auction = auction::findOrFail($id);

        foreach ($auction->images as $img) {
            Storage::disk('public')->delete($img->image_path);
            $img->delete();
        }

        $auction->delete();

        return response()->json(['message' => 'Auction deleted successfully']);
    }
}


