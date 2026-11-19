<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation for creating and updating a showroom model,
 * used by both the admin panel and the REST API.
 */
class ModelShowroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is already protected by the "auth" / "auth:sanctum" middleware.
        return true;
    }

    public function rules(): array
    {
        $creating = $this->isMethod('POST');

        // On update every field is optional so the API can send partial data.
        $presence = $creating ? ['required'] : ['sometimes', 'required'];

        return [
            'model_name' => [...$presence, 'string', 'max:100'],
            'year' => [...$presence, 'integer', 'between:1990,'.(date('Y') + 2)],
            'price' => [...$presence, 'numeric', 'min:0', 'max:99999999.99'],
            'image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['sometimes', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Please upload a picture of the vehicle.',
            'image.max' => 'The picture may not be larger than 2 MB.',
            'year.between' => 'Please enter a valid model year.',
        ];
    }
}
