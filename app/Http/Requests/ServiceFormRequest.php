<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class ServiceFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'name' => "string[]",
        'days' => "array",
        'products' => "array",
        'status' => "string",
        'hide_unavailable' => 'boolean',
        'visible_only_to_staff' => 'boolean',
    ])] public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'days' => ['array'],
            'hide_unavailable' => ['nullable'],
            'visible_only_to_staff' => ['nullable'],
            'status' => 'required',
            'type' => 'required',
        ];
    }
}
