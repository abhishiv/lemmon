<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingFormRequest extends FormRequest
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
    public function rules()
    {
        return [
            'vat' => ['required', 'decimal:0,1', 'min:0', 'max:50'],
            'takeaway_vat' => ['required', 'decimal:0,1', 'min:0', 'max:50'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'vat' => __('labels.vat'),
            'takeaway_vat' => __('labels.takeaway-vat'),
        ];
    }
}
