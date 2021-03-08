<?php

namespace Crater\Http\Requests;

use Crater\Models\InvoiceReturn;
use Crater\Rules\RelationNotExist;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteInvoiceReturnRequest extends FormRequest
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
            'ids' => [
                'required'
            ],
            'ids.*' => [
                'required',
                // Rule::exists('invoice_returns', 'id'),
                // new RelationNotExist(InvoiceReturn::class, 'payments')
            ]
        ];
    }
}
