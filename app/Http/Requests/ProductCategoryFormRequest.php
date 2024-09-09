<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class ProductCategoryFormRequest extends FormRequest
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
    #[ArrayShape(['name' => "string"])] public function rules(): array
    {
        $rules = [
            'name' => 'required|unique:product_categories,name,NULL,id,restaurant_id,' . auth()->user()->restaurant_id . ',deleted_at,NULL'
        ];
        if($this->isMethod('PUT')){
            $rules = [
                'name'      => 'required|unique:product_categories,name,' . $this->productCategory->id . ',id,restaurant_id,'. auth()->user()->restaurant_id .',deleted_at,NULL',
                'status'    => 'required',
                'products'  => 'nullable|array'
            ];
        }
        return $rules;
    }
}
