<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\contact_info;
use App\Helpers\ResponseHelper;

class contactinfo_controller extends Controller
{

    public function contacts()
    {
        $infos = contact_info::latest()->get(['id', 'platform', 'link']);
        return ResponseHelper::success('Contact info list', $infos);
    }

    public function add_contact(Request $request)
    {
        $validated = $request->validate([

            'platform' => 'required|string|max:255',
            'link' => 'required|string',
        ]);

        $info = contact_info::create($validated); // insert دفعة واحدة
        return ResponseHelper::success('Contact info created successfully', $info);
    }

    public function add_contacts(Request $request)
    {
        $validated = $request->validate([
            'contacts' => 'required|array|min:1',
            'contacts.*.platform' => 'required|string|max:255',
            'contacts.*.link' => 'required|string',
        ]);

        $inserted = contact_info::insert($validated['contacts']); // insert دفعة واحدة
        return ResponseHelper::success('Contact info created successfully', $validated['contacts']);
    }

    public function update_contact(Request $request, $id)
    {
        $info = contact_info::find($id);
        if (!$info) return ResponseHelper::error('Not found', null, 404);

        $validated = $request->validate([
            'platform' => 'required|string|max:255',
            'link' => 'required|string',
        ]);

        $info->update($validated);
        return ResponseHelper::success('Contact info updated successfully', $info);
    }

    public function delete_contact($id)
    {
        $info = contact_info::find($id);
        if (!$info) return ResponseHelper::error('Not found', null, 404);

        $info->delete();
        return ResponseHelper::success('Contact info deleted');
    }

}
