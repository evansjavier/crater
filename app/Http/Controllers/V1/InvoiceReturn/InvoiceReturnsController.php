<?php

namespace Crater\Http\Controllers\V1\Invoice;

use Illuminate\Http\Request;
use Crater\Http\Requests;
use Crater\Models\InvoiceReturn;
use Crater\Http\Controllers\Controller;
use Crater\Http\Requests\DeleteInvoiceRequest;
use Crater\Jobs\GenerateInvoicePdfJob;

class InvoiceReturnsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $invoices = InvoiceReturn::with(['items', 'user', 'creator', 'invoiceTemplate', 'taxes'])
            ->join('users', 'users.id', '=', 'invoices_returns.user_id')
            ->applyFilters($request->only([
                'status',
                'paid_status',
                'customer_id',
                'invoice_id',
                'invoice_number',
                'from_date',
                'to_date',
                'orderByField',
                'orderBy',
                'search',
            ]))
            ->whereCompany($request->header('company'))
            ->select('invoices_returns.*', 'users.name')
            ->latest()
            ->paginateData($limit);

        return response()->json([
            'invoices_returns' => $invoices,
            'invoiceReturnTotalCount' => $limit == 'all' ? $invoices['data']->count() : $invoices->total()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Requests\InvoicesReturnsRequest $request)
    {
        $invoice = InvoiceReturn::createInvoiceReturn($request);

        if ($request->has('invoiceSend')) {
            $invoice->send($request->subject, $request->body);
        }

        // GenerateInvoicePdfJob::dispatch($invoice);

        return response()->json([
            'invoice_return' => $invoice
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Crater\Models\Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(InvoiceReturn $invoice)
    {
        $invoice->load([
            'items',
            'items.taxes',
            'user',
            'invoiceTemplate',
            'taxes.taxType',
            'fields.customField'
        ]);

        $siteData = [
            'invoice' => $invoice,
            'nextInvoiceNumber' => $invoice->getInvoiceNumAttribute(),
            'invoicePrefix' => $invoice->getInvoicePrefixAttribute(),
        ];

        return response()->json($siteData);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Requests\InvoicesReturnsRequest $request, InvoiceReturn $invoice)
    {
        $invoice = $invoice->updateInvoice($request);

        GenerateInvoicePdfJob::dispatch($invoice, true);

        return response()->json([
            'invoice' => $invoice,
            'success' => true
        ]);
    }

    /**
     * delete the specified resources in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteInvoiceRequest $request)
    {
        InvoiceReturn::destroy($request->ids);

        return response()->json([
            'success' => true
        ]);
    }
}
