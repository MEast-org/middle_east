<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\job_opportunity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;
use Carbon\Carbon;

class user_opportunity_controller extends Controller
{

    public function __construct()
    {
        $this->middleware('user_company_auth');
    }


    public function my_opportunities()
    {
        $user = auth()->user();
        if (!$user) return ResponseHelper::error('Unauthorized', null, 401);

        $jobs = job_opportunity::where('publisher_type', $user instanceof \App\Models\company ? 'company' : 'user')
            ->where('publisher_id', $user->id)
            ->latest()
            ->paginate(request('page_size', 10));

        return ResponseHelper::success('My jobs', $jobs);
    }

    public function my_opportunity($id)
    {
        $user = auth()->user();
        $job = job_opportunity::find($id);
        if (!$job) return ResponseHelper::error('not found', null, 404);

        if (!$user || !($job->publisher_id == $user->id && $job->publisher_type == (get_class($user) === 'App\\Models\\company' ? 'company' : 'user'))) {
            return ResponseHelper::error('Forbidden', null, 403);
        }

        return ResponseHelper::success('Job details', $job);
    }


    public function add_opportunity(Request $request)
    {
        $user = auth()->user();
        if (!$user) return ResponseHelper::error('Unauthorized', null, 401);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date|after_or_equal:today',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'type' => 'required|in:full_time,part_time,contract,internship,remote',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|string',
        ]);

        $validated['publisher_type'] = $user instanceof \App\Models\company ? 'company' : 'user';
        $validated['publisher_id'] = $user->id;

        $job = job_opportunity::create($validated);
        return ResponseHelper::success('Created successfully', $job);
    }

    public function update_opportunity(Request $request, $id)
   {
    $user = auth()->user();
    $job = job_opportunity::find($id);
    if (!$job) return ResponseHelper::error('not found', null, 404);

    if (!$user || !($job->publisher_id == $user->id && $job->publisher_type == (get_class($user) === 'App\\Models\\company' ? 'company' : 'user'))) {
        return ResponseHelper::error('Forbidden', null, 403);
    }

    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'category_id' => 'sometimes|required|exists:categories,id',
        'country_id' => 'sometimes|nullable|exists:countries,id',
        'region_id' => 'sometimes|nullable|exists:regions,id',
        'description' => 'sometimes|nullable|string',
        'starts_at' => 'sometimes|nullable|date|after_or_equal:today',
        'expires_at' => 'sometimes|nullable|date|after_or_equal:starts_at',
        'type' => 'sometimes|required|in:full_time,part_time,contract,internship,remote',
        'min_salary' => 'sometimes|nullable|numeric|min:0',
        'max_salary' => 'sometimes|nullable|numeric|min:0|gte:min_salary',
        'social_links' => 'sometimes|nullable|array',
        'social_links.*' => 'nullable|string',
    ]);

    $job->update($validated);
    return ResponseHelper::success('Updated successfully', $job);
    }



    public function delete_opportunity($id)
    {
        $user = auth()->user();
        $job = job_opportunity::find($id);
        if (!$job) return ResponseHelper::error('not found', null, 404);

        if (!$user || !($job->publisher_id == $user->id && $job->publisher_type == (get_class($user) === 'App\\Models\\company' ? 'company' : 'user'))) {
            return ResponseHelper::error('Forbidden', null, 403);
        }

        $job->delete();
        return ResponseHelper::success('Deleted successfully');
    }






}
