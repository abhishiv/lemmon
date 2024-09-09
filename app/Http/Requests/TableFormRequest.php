<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class TableFormRequest extends FormRequest
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
        'name' => "string",
    ])] public function rules(): array
    {
        $rules = [
            'name'      => 'required|unique:restaurant_tables,name,NULL,id,restaurant_id,' . auth()->user()->restaurant_id,
            'status'    => 'required',
            'type'      => ['required', Rule::in(RestaurantTable::TYPES)],
            'room' => 'nullable|max:500',
            'optional'  => 'nullable|max:500'
        ];

        if ($this->isMethod('PUT')) {
            $rules['name'] = 'required|unique:restaurant_tables,name,' . $this->table->id . ',id,restaurant_id,'. auth()->user()->restaurant_id;
            $rules['type'] = ['required', Rule::in(RestaurantTable::TYPES)];
        }

        return $rules;
    }
}
