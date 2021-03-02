<?php
namespace Crater\Http\Requests;

use Crater\Models\InvoiceReturn;
use Crater\Rules\UniqueNumber;
use Illuminate\Foundation\Http\FormRequest;

class InvoicesReturnsRequest extends FormRequest
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
     * Get the validation rules that apply to the request.s
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'invoice_date' => [
                'required'
            ],
            'due_date' => [
                'required'
            ],
            'user_id' => [
                'required'
            ],
            'invoice_number' => [
                'required',
                new UniqueNumber(InvoiceReturn::class, null, $this->header('company'))
            ],
            'discount' => [
                'required'
            ],
            'discount_val' => [
                'required'
            ],
            'sub_total' => [
                'required'
            ],
            'total' => [
                'required'
            ],
            'tax' => [
                'required'
            ],
            'invoice_template_id' => [
                'required'
            ],
            'items' => [
                'required',
                'array'
            ],
            'items.*' => [
                'required',
                'max:255'
            ],
            'items.*.description' => [
                'max:255'
            ],
            'items.*.name' => [
                'required'
            ],
            'items.*.quantity' => [
                'required'
            ],
            'items.*.price' => [
                'required'
            ]
        ];

        if ($this->isMethod('PUT')) {
            $rules['invoice_return_number'] = [
                'required',
                new UniqueNumber(InvoiceReturn::class, $this->route('invoice_return')->id, $this->header('company'))
            ];
        }

        return $rules;
    }
}
