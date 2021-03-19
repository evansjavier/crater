<?php
namespace Crater\Http\Requests;

use Crater\Models\InvoiceReturn;
use Crater\Rules\UniqueNumber;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceReturnsRequest extends FormRequest
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
            'return_date' => [
                'required'
            ],
            'user_id' => [
                'required'
            ],
            'invoice_return_number' => [
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

            // dd($this->route('invoices_return')->id);
            $rules['invoice_return_number'] = [
                'required',
                new UniqueNumber(InvoiceReturn::class, $this->route('invoices_return')->id, $this->header('company'))
            ];
        }

        return $rules;
    }
}
