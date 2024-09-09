<?php

namespace App\Http\Requests;

use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class UserFormRequest extends FormRequest
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
    #[ArrayShape(['email' => "string", 'phone' => "string"])]
    public function rules(): array
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|unique:users,phone',
            'staff_type' => Rule::in(User::STAFFTYPES),
            'tables' => 'array',
        ];

        if ($this->isMethod('PUT')) {
            $rules['email'] = 'required|email|unique:users,email,' . $this->user->id;
            $rules['phone'] = 'nullable|unique:users,phone,' . $this->user->id;
        }

        return $rules;
    }
}
