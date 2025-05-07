<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\favorite;
use App\Helpers\ResponseHelper;


class favorite_controller extends Controller
{


    public function __construct()
    {
        $this->middleware('user_company_auth');
    }


    // إضافة للمفضلة
    public function add(Request $request)
    {
        $request->validate([
            'favorable_type' => 'required|in:ads,job_opportunity,auction',
            'favorable_id' => 'required|integer',
        ]);

        $user = auth()->user();

        $exists = favorite::where([
            'favoriter_id' => $user->id,
            'favoriter_type' => $user instanceof \App\Models\company ? 'company' : 'user',
            'favorable_id' => $request->favorable_id,
            'favorable_type' => $request->favorable_type,
        ])->first();

        if ($exists) {
            return ResponseHelper::error('Item is already in favorites.', 409);
        }

        favorite::create([
            'favoriter_id' => $user->id,
            'favoriter_type' => $user instanceof \App\Models\company ? 'company' : 'user',
            'favorable_id' => $request->favorable_id,
            'favorable_type' => $request->favorable_type,
        ]);

        return ResponseHelper::success('Added to favorites successfully.');
    }

    // حذف من المفضلة عبر ID
    public function remove($id)
    {
        $user = auth()->user();

        $favorite = favorite::find($id);

        if (!$favorite) {
            return ResponseHelper::error('favorite not found.', 404);
        }

        // تحقق من أن المستخدم هو صاحب هذا السجل
        if (
            $favorite->favoriter_id !== $user->id ||
            $favorite->favoriter_type !== (get_class($user) === 'App\\Models\\company' ? 'company' : 'user')
        ) {
            return ResponseHelper::error('Unauthorized to delete this favorite.', 403);
        }

        $favorite->delete();

        return ResponseHelper::success('Removed from favorites successfully.');
    }

    // عرض قائمة المفضلات
    public function list()
    {
        $user = auth()->user();
        // $favorites = favorite::where([
        //         'favoriter_id' => $user->id,
        //         'favoriter_type' => (get_class($user) === 'App\\Models\\company' ? 'company' : 'user'),
        //     ])
        $favorites= $user->favorites()->with('favorable') // استخدام العلاقة morph
            ->latest()
            ->paginate(request('page_size', 10));

        return ResponseHelper::success(' favorites retrived successfully.',$favorites);
    }
}
