<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Console\Input\Input;

class ProductFormRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Skip slug manipulation if the product already exists
        if ($this->product != null) {
            return;
        }

        $attributes = $this->all();

        $slug = $attributes['slug'];


        $count = Product::where('slug', 'LIKE', $slug . '%')->count();

        if ($count != 0) {
            $slug = $slug . '-' . ($count + 1);
        }

        $attributes['slug'] = $slug;
        $this->replace($attributes);
    }


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
    public function rules(): array
    {
        //check if the minlimit is less than count of products.*.product
        $product = $this->product ?? null;
        return [
            'name' => 'required',
            'price' => 'required|numeric',
            'special_price' => 'lt:price|numeric|nullable',
            'type' => 'required|alpha',
            'weight' => 'nullable',
            'is_custom' => 'boolean',
            'additional_info' => 'nullable|max:50',
            'slug' => [
                'sometimes',
                'required',
                Rule::unique('products', 'slug')->where('restaurant_id',
                    auth()->user()->restaurant_id)->whereNull("deleted_at")->ignore($product->id ?? null)
            ],
            'description' => 'max:300',
            'status' => 'required|in:' . implode(',', Product::STATUSES),
            'category_id' => 'required',
            'related_id' => 'array|nullable',
            'food_type_id' => ['required_if:type,food', 'exists:App\Models\FoodType,id'],
            'extra' => ['array'],
            'extra.*.groupname' => ['required', 'string'],
            'extra.*.grouplimit' => ['numeric'],
            'extra.*.minlimit' => [
                'lte:extra.*.grouplimit',
                function ($attribute, $value, $fail) {
                    preg_match('/\d+/', $attribute, $matches);

                    // Count the number of 'product.*.products' for the current 'product' group
                    $productCount = count(request()->input("extra.{$matches[0]}.extras", []));

                    // Compare 'minlimit' with the count of 'product.*.products'
                    if ($value > $productCount) {
                        $fail("The minimum limit must be less than or equal to the number of items in this group.");
                    }
                },
            ],
            'extra.*.extras' => ['required', 'array'],
            'extra.*.extras.*.id' => ['required', 'exists:App\Models\Extra,id'],
            'extra.*.extras.*.price' => ['required', 'numeric'],
            'extra.*.extras.*.order' => ['required', 'numeric'],
            'product' => ['array'],
            'product.*.groupname' => ['required', 'string'],
            'product.*.grouplimit' => ['numeric'],
            'product.*.minlimit' => [
                'lte:product.*.grouplimit',
                function ($attribute, $value, $fail) {
                    preg_match('/\d+/', $attribute, $matches);

                    // Count the number of 'product.*.products' for the current 'product' group
                    $productCount = count(request()->input("product.{$matches[0]}.products", []));

                    // Compare 'minlimit' with the count of 'product.*.products'
                    if ($value > $productCount) {
                        $fail("The minimum limit must be less than or equal to the number of items in this group.");
                    }
                },

            ],
            'product.*.products' => ['required', 'array'],
            'product.*.products.*.id' => ['required', 'exists:App\Models\Product,id'],
            'product.*.products.*.price' => ['required', 'numeric'],
            'product.*.products.*.order' => ['required', 'numeric'],
            'removable' => ['array'],
            'removable.*.groupname' => ['required', 'string'],
            'removable.*.grouplimit' => ['required', 'numeric'],
            'removable.*.removables' => ['required', 'array'],
            'removable.*.removables.*.id' => ['required', 'exists:App\Models\Extra,id'],
            'removable.*.removables.*.order' => ['required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'extra.*.groupname' => 'A group name for extra is required',
            'extra.*.grouplimit.numeric' => 'A numeric group limit for extra is required',
            'extra.*.minlimit.lte' => 'Minimum limit should be less than or equal to the maximum limit',
            'extra.*.minlimit.numeric' => 'A numeric minimum limit for extra is required',
            'extra.*.extras' => 'Please add at least one extra in the group',
            'extra.*.extras.*.id' => 'Please add at least one extra in the group',
            'extra.*.extras.*.price' => 'A numeric extra price is required',
            'extra.*.extras.*.order' => 'A numeric extra order is required',
            'product.*.groupname' => 'A group name for product is required',
            'product.*.grouplimit.numeric' => 'A numeric group limit for product is required',
            'product.*.minlimit.lte' => 'Minimum limit should be less than or equal to the maximum limit',
            'product.*.minlimit.numeric' => 'A numeric minimum limit for product is required',
            'product.*.products' => 'Please add at least one product in the group',
            'product.*.products.*.id' => 'Please add at least one product in the group',
            'product.*.products.*.price' => 'A numeric product price is required',
            'product.*.products.*.order' => 'A numeric product order is required',
            'removable.*.groupname' => 'A group name for removable is required',
            'removable.*.grouplimit' => 'A numeric group limit for removable is required',
            'removable.*.removables' => 'Please add at least one removeable in the group',
            'removable.*.removables.*.id' => 'Please add at least one removeable in the group',
            'removable.*.removables.*.order' => 'A numeric removable order is required',
        ];
    }
}
