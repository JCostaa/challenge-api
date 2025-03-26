<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetSearch extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
