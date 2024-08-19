<?php

namespace App\Http\Requests;

use App\Enums\StoreTypes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:5', 'max:100'],
            'latitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['string', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'status' => ['required', 'in:X,O'],
            'store_type' => ['required', Rule::enum(StoreTypes::class)],
            'max_delivery_distance' => ['required', 'numeric', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * Custom error messages
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'lat.regex' => 'Invalid latitude value.',
            'long.regex' => 'Invalid longitude value.',
            'status.in' => 'Invalid Status value. Allowed values = X or O.',
        ];
    }
}
