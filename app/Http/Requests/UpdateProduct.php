<?php

namespace App\Http\Requests;

use App\Models\Enum\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProduct extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['nullable', new Enum(ProductStatus::class)],
            'url' => 'nullable|active_url',
            'creator' => 'nullable|string',
            'created_t' => 'nullable|numeric',
            'last_modified_t' => 'nullable|numeric',
            'product_name' => 'nullable|string',
            'quantity' => 'nullable|string',
            'brands' => 'nullable|string',
            'categories' => 'nullable|string',
            'labels' => 'nullable|string',
            'cities' => 'nullable|string',
            'purchase_places' => 'nullable|string',
            'stores' => 'nullable|string',
            'ingredients_text' => 'nullable|text',
            'traces' => 'nullable|string',
            'serving_size' => 'nullable|string',
            'serving_quantity' => 'nullable|double',
            'nutriscore_score' => 'nullable|double',
            'nutriscore_grade' => 'nullable|string|max:1',
            'main_category' => 'nullable|string',
            'image_url' => 'nullable|active_url',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
