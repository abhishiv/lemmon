<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class RestaurantSettingsFormRequest extends FormRequest
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
        $rules = [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],

            'kitchen_start_time' => ['array'],
            'kitchen_end_time' => ['array'],

            'bar_start_time' => ['array'],
            'bar_end_time' => ['array'],

            'tip_recommended_amount_1' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:2555'],
            'tip_recommended_amount_2' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:2555'],
            'tip_recommended_amount_3' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:2555'],

            'order_grouping_popup' => ['nullable', Rule::in(['true'])],
            'group_order_delay' => ['nullable', 'integer', 'min:0', 'max:59'],

            'take_away' => ['nullable'],
            'discount_takeaway' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],

            'take_away_auto_close' => ['nullable'],
            'take_away_auto_close_interval' => ['nullable', 'numeric', 'min:5', 'max:100'],

            'delivery' => ['nullable'],
        ];

        if ($this->has('delivery_cities')) {
            $rules['delivery_cities.*'] = 'string';
        }

        if ($this->has('delivery_cities')) {
            $rules = array_merge($rules, [
                'delivery_cities.*' => ['required', 'string'],
                'delivery_fees.*' => ['required', 'numeric', 'min:0'],
            ]);
        }

        if ($this->has('ip')) {
            $rules = array_merge($rules, [
                'ip.*' => ['required', 'string'],
                'port.*' => ['required', 'integer'],
                'device-id.*' => ['required', 'string'],
                'print-type.*' => ['required', 'string'],
            ]);
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        $printersData = $this->only('ip', 'port', 'device-id', 'print-type');

        $printers = [];

        foreach ($printersData['ip'] as $key => $ip) {
            $printers[] = (object)[
                'ip' => $ip,
                'port' => $printersData['port'][$key],
                'port' => $printersData['port'][$key],
                'device_id' => $printersData['device-id'][$key],
                'print_type' => $printersData['print-type'][$key],
            ];
        }

        if (count($printers)) {
            $this->session()->flash('printers', $printers);
        }

        $deliveryCitiesData = $this->only('delivery_cities', 'delivery_fees');

        $deliveryCities = [];

        foreach ($deliveryCitiesData['delivery_cities'] as $key => $name) {
            $deliveryCities[] = (object)[
                'name' => $name,
                'fee' => $deliveryCitiesData['delivery_fees'][$key],
            ];
        }

        if (count($deliveryCities)) {
            $this->session()->flash('delivery_cities', $deliveryCities);
        }

        throw new ValidationException($validator);
    }

    public function attributes()
    {
        return [
            'ip.*' => __('labels.ip'),
            'port.*' => __('labels.port'),
            'device-id.*' => __('labels.device-id'),
            'print-type.*' => __('labels.print'),
            'delivery_cities.*' => __('manager/settings.city'),
            'delivery_fees.*' => __('labels.delivery-fee'),
        ];
    }
}
