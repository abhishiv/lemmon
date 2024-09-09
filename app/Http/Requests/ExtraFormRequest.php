<?php

namespace App\Http\Requests;

use App\Models\Extra;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;
use Illuminate\Foundation\Http\FormRequest;

class ExtraFormRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Skip title manipulation if the extra already exists
        if($extra = $this->extra ?? null) return;

        $attributes = $this->all();

        $title = $attributes['title'];


        $count = Extra::where('title', 'LIKE', $title.'%')->count();

        if($count != 0) {
            $title = $title.'-'.strval($count + 1);
        }

        $attributes['title'] = $title;
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
    #[ArrayShape([
        'title'          => "string",
        'description'    => "string",
    ])] public function rules()
    {
        return [
            'title'          => ['required', Rule::unique('extras', 'title')->where('restaurant_id', auth()->user()->restaurant_id)->ignore($this->extra->id ?? null)],
            'status' => ['required', Rule::in(Extra::STATUSES)],
            'description'    => 'max:300',
        ];
    }
}
