<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Illuminate\Support\Facades\Hash;
use App\Http\Requests\fieldRequest;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\custom_field;
use App\Models\custom_field_value;
use App\Models\category;


class customfield_controller extends Controller
{

      /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }
        // عرض جميع الحقول
        public function custom_fields()
        {
            $fields = custom_field::with('category')->get();
            return response()->json([
                'fields'=>$fields
            ],201);
        }




        public function category_fields($category_id)
         {

            $category = category::with('customfields')->findOrFail($category_id);

            return response()->json([
                'success' => true,
                'category' => $category,
            ],201);


        }

        // عرض حقل معين
        public function view_field(custom_field $field)
        {
            return response()->json(['field'=>$field->load('category')],201);
        }

        // إنشاء حقل جديد
        public function add_field(fieldRequest $request)
        {
                // تحويل FormData إلى array
            $data = $request->all();

            // معالجة options إذا كانت JSON string
            if ($request->has('options') && is_string($request->options)) {
                $data['options'] = json_decode($request->options, true);
            }

            $field = custom_field::create($data);

            if ($request->hasFile('custom_icon')) {
           $field->custom_icon  = $request->file('custom_icon')->store('custom_icons', 'public');
           $field->save();
            }


            return response()->json([
                'success' => true,
                'data' => $field
            ], 201);
        }

        // تحديث حقل موجود
        public function update_field(fieldRequest $request,custom_field $field)
        {
            $data = $request->all();

               // معالجة options إذا كانت JSON string
               if ($request->has('options') && is_string($request->options)) {
                $data['options'] = json_decode($request->options, true);
            }



            if ($request->has('type') && $request->type !== $field->type) {
                switch ($request->type) {
                    case 'text':
                    case 'textarea':
                        // نمسح حقول الأنواع الأخرى
                        $data['options'] = null;
                        break;

                    case 'select':
                    case 'checkbox':
                    case 'radio':
                        $data['min_length'] = null;
                        $data['max_length'] = null;
                        break;

                    case 'file':
                        $data['min_length'] = null;
                        $data['max_length'] = null;
                        $data['options'] = null;
                        break;

                    case 'date':
                    case 'number':
                        $data['min_length'] = null;
                        $data['max_length'] = null;
                        $data['options'] = null;
                        break;
                }
            }


            $field->update($data);
            if ($request->hasFile('custom_icon')) {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($field->custom_icon) {
                    Storage::disk('public')->delete($field->custom_icon);
                }
                $field->custom_icon = $request->file('custom_icon')->store('custom_icons', 'public');
                $field->save();
            }

            return response()->json([
                'message' => 'success',
                'data' => $field
            ], 201);
        }

        // حذف حقل
        public function delete_field(custom_field $field)
        {
            if ($field->custom_icon) {
                Storage::disk('public')->delete($field->custom_icon);
            }
            $field->delete();

            return response()->json([
                'message' => 'done'
            ],201);
        }



        ///////////////////////  filed value processing ///////////////////////


public function addupdate_fieldvalue(Request $request)
{
    $validator = Validator::make($request->all(), [
        'owner_type' => 'required|in:job_opportunity,ads',
        'owner_id' => 'required|integer',
        'fields' => 'required|array',
    ]);

    $validator->after(function ($validator) use ($request) {
        $ownerType = $request->input('owner_type');
        $ownerId = $request->input('owner_id');

        $map = Relation::morphMap();

        if (!isset($map[$ownerType])) {
            $validator->errors()->add('owner_type', 'Invalid owner_type provided.');
            return;
        }
        $modelClass = $map[$ownerType];

        if (!$modelClass::where('id', $ownerId)->exists()) {
            $validator->errors()->add('owner_id', 'The selected owner_id does not exist in the ' . $ownerType . ' table.');
        }
    });

    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    }
    //     return response()->json(['owner type' => $request->owner_type,'owner id' => $request->owner_id],200);



    $ownerTypeInput = $request->input('owner_type'); // مثل: "ads"
    $ownerType = Relation::getMorphedModel($ownerTypeInput); // مثل: App\Models\ads
    $ownerId = $request->input('owner_id');
    $fields = $request->input('fields', []);

    // دمج الملفات مع الحقول
    foreach ($request->file('fields', []) as $fieldId => $file) {
        $fields[$fieldId] = $file;
    }

    $savedFields = [];

    foreach ($fields as $fieldId => $inputValue) {
        $customField = custom_field::find($fieldId);
        if (!$customField) continue;

        $isRequired = $customField->is_required;
        $type = $customField->type;
        $valueToStore = null;

        // البحث عن قيمة موجودة
        $existingValue = custom_field_value::where([
            'custom_field_id' =>$customField->id,
            'owner_table_type' =>$ownerTypeInput,
            'owner_table_id' =>$ownerId,
        ])->first();

        // معالجة أنواع الحقول
        if ($type === 'file') {
            $file = $request->file("fields.{$fieldId}");
            if ($isRequired && !$file) {
                return response()->json([
                    'status' => false,
                    'message' => "No file uploaded for {$customField->en_name}"
                ], 422);
            }

            if ($file && $file->isValid()) {
                $path = $file->store('custom_fields', 'public');
                if ($existingValue && $existingValue->value) {
                    Storage::disk('public')->delete($existingValue->value);
                }
                $valueToStore = $path;
            }
        } elseif ($type === 'checkbox') {
            $valueArray = is_array($inputValue) ? $inputValue : [$inputValue];
            if ($isRequired && empty($valueArray)) {
                return response()->json([
                    'status' => false,
                    'message' => "{$customField->en_name} is required"
                ], 422);
            }
            $valueToStore = $valueArray;
        } else {
            if ($isRequired && (is_null($inputValue) || $inputValue === '')) {
                return response()->json([
                    'status' => false,
                    'message' => "{$customField->en_name} is required"
                ], 422);
            }
            $valueToStore = $inputValue;
        }


        //return response()->json(['owner type' => $ownerTypeInput,'owner id' =>$ownerId],200);
        // حفظ القيمة
        $custom_field_value = custom_field_value::updateOrCreate(
            [
                'custom_field_id' =>$customField->id,
                'owner_table_type' =>$ownerTypeInput,
                'owner_table_id' =>$ownerId,
            ],
            [
                'value' => $valueToStore,
            ]
        );

        if ($custom_field_value) {
            $custom_field_value->field_info = $customField;
            $savedFields[] = $custom_field_value;
        }
    }

    return response()->json([
        'status' => true,
        'custom_field_values' => $savedFields
    ]);
}

    }

