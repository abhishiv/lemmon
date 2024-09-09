<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestaurantDetailsRequest extends FormRequest
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
    public function rules(): array
    {
        $userId = auth()->id();
        $restaurantId = $this->restaurant->id;

        $rules = [
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')->ignore($userId), Rule::unique('restaurants')->whereNull('deleted_at')->ignore($restaurantId)],
            'contact_person' => 'required',
            'phone' => ['required', 'numeric', "unique:users,phone,{$userId}", "unique:restaurants,phone,{$restaurantId}"],
            'address' => ['required', 'string', 'max:100'],
            'company_registration' => "required||unique:restaurants,company_registration,{$restaurantId}",
            'receipt_phone' => ['nullable', 'string', 'max:48'],
            'receipt_message' => ['nullable', 'string', 'max:48'],
        ];

        return $rules;
    }
}
