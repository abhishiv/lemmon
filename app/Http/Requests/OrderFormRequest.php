<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class OrderFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'orderID' => "integer",
        'itemID' => "integer",
        'orderStatus' => "string",
        'itemStatus' => "string",
        'type'     => "string",
    ])] public function rules(): array
    {
        return [
            'orderID' => ['required', 'integer'],
            'orderStatus' => ['sometimes', 'string', Rule::in(Order::CHOOSESTATUS)],
            'type' => ['sometimes', 'nullable', 'string', Rule::in(['restaurant', 'bar'])],
            'food_type_id' => ['nullable', 'integer', 'exists:food_types,id'],
        ];
    }
}
