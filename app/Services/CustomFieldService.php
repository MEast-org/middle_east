<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\custom_field;
use App\Models\custom_field_value;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CustomFieldService
{
    public function handle(Request $request, int $ad_id ,int $category_id): array
    {
        $fields = $request->input('fields', []);

        // دمج الملفات مع الحقول
        foreach ($request->file('fields', []) as $fieldId => $file) {
            $fields[$fieldId] = $file;
        }

        // جميع الحقول المرتبطة بهذه الفئة من الإعلان
        $requiredFields = custom_field::where('is_required', true)
        ->where('category_id', $category_id)
        ->get();

        $missingFields = [];

        // التحقق من الحقول المطلوبة إن كانت مفقودة من الطلب
        foreach ($requiredFields as $requiredField) {
            if (!array_key_exists($requiredField->id, $fields)) {
                $missingFields["fields.{$requiredField->id}"] = "{$requiredField->en_name} is required";
            }
        }

        if (!empty($missingFields)) {
            throw ValidationException::withMessages($missingFields);
        }

        $savedFields = [];

        foreach ($fields as $fieldId => $inputValue) {
            $customField = custom_field::find($fieldId);
            if (!$customField) continue;

            $isRequired = $customField->is_required;
            $type = $customField->type;
            $valueToStore = null;

            $existingValue = custom_field_value::where([
                'custom_field_id' => $customField->id,
                'ad_id' => $ad_id,
            ])->first();

            if ($type === 'file') {
                $file = $request->file("fields.{$fieldId}");
                if ($isRequired && !$file) {
                    throw ValidationException::withMessages([
                        "fields.{$fieldId}" => "{$customField->en_name} is required"
                    ]);
                }

                if ($file && $file->isValid()) {
                    $path = $file->store('custom_fields', 'public');
                    if ($existingValue && $existingValue->value) {
                        Storage::disk('public')->delete($existingValue->value);
                    }
                    $valueToStore = $path;
                }
            } elseif ($type === 'checkbox') {
                if (is_string($inputValue)) {
                    $decoded = json_decode($inputValue, true);
                    $inputValue = is_array($decoded) ? $decoded : [$inputValue];
                }

                $valueArray = is_array($inputValue) ? $inputValue : [$inputValue];

                if ($isRequired && empty($valueArray)) {
                    throw ValidationException::withMessages([
                        "fields.{$fieldId}" => "{$customField->en_name} is required"
                    ]);
                }

                $valueToStore = $valueArray;
            } else {
                if ($isRequired && (is_null($inputValue) || $inputValue === '')) {
                    throw ValidationException::withMessages([
                        "fields.{$fieldId}" => "{$customField->en_name} is required"
                    ]);
                }

                $valueToStore = $inputValue;
            }

            $saved = custom_field_value::updateOrCreate(
                [
                    'custom_field_id' => $customField->id,
                    'ad_id' => $ad_id,
                ],
                [
                    'value' => $valueToStore,
                ]
            );

            if ($saved) {
                $savedFields[] = $saved;
            }
        }

        return $savedFields;
    }
}
