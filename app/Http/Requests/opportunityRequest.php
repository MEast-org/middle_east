<?php
// app/Http/Requests/JobOpportunityRequest.php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class opportunityRequest extends FormRequest
{
    public function rules()
    {
        return [
            'en_name' => 'sometimes|required|string|max:255',
            'ar_name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'company_id' => 'sometimes|required|exists:companies,id',
            'country_id' => 'sometimes|nullable|exists:countries,id',
            'region_id' => 'sometimes|nullable|exists:regions,id',
            'description' => 'sometimes|nullable|string',
            'expires_at' => 'sometimes|nullable|date|after_or_equal:today',
            'type' => 'sometimes|required|in:full_time,part_time,contract,internship,remote',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
