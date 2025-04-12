<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class fieldRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'category_id' => 'sometimes|required|exists:categories,id',
            'ar_name' => 'sometimes|required|string|max:100',
            'en_name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|in:text,number,textarea,select,checkbox,radio,date,file',
            'is_required' => 'sometimes|boolean',
            'options' => 'sometimes|nullable|array|json',
            'custom_icon' =>'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];

        switch ($this->input('type')) {
            case 'text':
            case 'textarea':
                $rules['min_length'] = 'required|integer|min:1';
                $rules['max_length'] = 'required|integer|gt:min_length';
                $rules['options'] = 'nullable';
                break;

            case 'select':
            case 'checkbox':
            case 'radio':
                $rules['options'] = 'required|array|min:2';
                $rules['options.*'] = 'required|string';
                break;

            case 'file':
                $rules['options'] = 'nullable';
                break;
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'min_length.required' => 'min length is 1',
            'options.required' => 'options is required',
            'max_length.gt' => 'max must be greater than min'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_required' => $this->is_required ?? true
        ]);

        if ($this->has('options')) {
            if (is_null($this->options) || $this->options === 'null') {
                $this->merge(['options' => null]);
            } elseif (is_string($this->options)) {
                $this->merge([
                    'options' => json_decode($this->options, true)
                ]);
            }
        }
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
