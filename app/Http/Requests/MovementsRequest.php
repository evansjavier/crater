<?php

namespace Crater\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovementsRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'item_id' => ['required', 'exists:items,id'],
            'movement_date' => ['required', 'date'],
            'movement_type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'min:0', 'numeric']
        ];
    }
}
