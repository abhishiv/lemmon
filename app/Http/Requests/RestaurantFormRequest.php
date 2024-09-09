<?php

namespace App\Http\Requests;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class RestaurantFormRequest extends FormRequest
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
        'email' => "array",
        'contact_person' => "string",
        'phone' => "string",
        'vat' => "string",
        'bank_account' => "string",
        'payment_fee' => "string",
    ])] public function rules(): array
    {
        $rules = [
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at'), Rule::unique('restaurants')->whereNull('deleted_at')],
            'contact_person' => 'required',
            'phone' => 'required|unique:users,phone|unique:restaurants,phone|numeric',
            'address' => ['required', 'string', 'max:100'],
            'vat' => 'nullable',
            'company_registration' => 'required||unique:restaurants,company_registration',
            'payrexx_token' => 'required|unique:restaurants,payrexx_token|string|size:30',
            'payrexx_name' => 'required|unique:restaurants,payrexx_name|string',
            'receipt_message' => ['nullable', 'string', 'max:48'],
            'slug' => ['required', 'unique:restaurants,slug', 'regex:/^[a-z0-9]+(-?[a-z0-9]+)*$/i'],
            'status' => 'nullable',
        ];

        if ($this->isMethod('PUT')) {
            $user = $this->restaurant->manager;
            $id = $this->restaurant->id;

            $rules['payrexx_token'] = "required|string|size:30|unique:restaurants,payrexx_token,{$id}";
            $rules['payrexx_name'] = "required|string|unique:restaurants,payrexx_name,{$id}";
            $rules['slug']  = ['nullable'];
            $rules['company_registration'] = "required|unique:restaurants,company_registration,{$id}";

            if ($user) {
                $rules['email'] = ['required', 'email', Rule::unique('users')->whereNull('deleted_at')->ignore($user->id), Rule::unique('restaurants')->whereNull('deleted_at')->ignore($id)];
                $rules['phone'] = 'required|numeric|unique:users,phone,' . $user->id . "|unique:restaurants,phone,{$id}";
            } else {
                $rules['email'] = ['required', 'email', Rule::unique('restaurants')->whereNull('deleted_at')->ignore($id)];
                $rules['phone'] = 'required|numeric|unique:restaurants,phone,' . $id;
            }
        }
        return $rules;
    }
}
