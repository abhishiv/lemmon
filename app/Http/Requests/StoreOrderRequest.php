<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
        $rules = [];

        $rules['service_method'] = ['required', Rule::in([Order::DINEIN, Order::TAKEAWAY, Order::DELIVERY])];

        $table = RestaurantTable::find(session('table.id'));

        if ($this->input('service_method') === Order::DELIVERY) {

            $deliveryCities = restaurant_settings_get('delivery_cities', $table->restaurant);

            $cityNames = [];

            if (is_array($deliveryCities)) {
                foreach($deliveryCities as $city) {
                    $cityNames[] = $city->name;
                }
            }

            $rules['customer_type'] = ['sometimes', Rule::in(['individual', 'company'])];

            if ($this->input('customer_type') === 'company') {
                $rules['company_name'] = ['required', 'string'];
            } else {
                $rules['first_name'] = ['required', 'string'];
                $rules['last_name'] = ['required', 'string'];
            }

            $rules['phone'] = ['required', 'string'];
            $rules['street'] = ['required', 'string'];
            $rules['postal_code'] = ['required', 'string'];
            $rules['city'] = ['required', Rule::in($cityNames)];
            $rules['email'] = ['required', 'email:rfc,dns'];
            $rules['delivery_notes'] = ['max:255'];
        } else if ($this->input('service_method') === Order::TAKEAWAY && $table->type === RestaurantTable::OFFSITE) {

            $rules['pickup'] = ['required'];

            if ($this->input('pickup') === 'later') {
                $rules['first_name'] = ['required', 'string'];
                $rules['last_name'] = ['required', 'string'];
                $rules['phone'] = ['required', 'string'];
                $rules['email'] = ['required', 'email:rfc,dns'];
                $rules['pickup_day'] = ['required', 'string'];
                $rules['pickup_time'] = ['required', 'string'];
            }
        }

        return $rules;
    }
}
