<?php

namespace Crater\Http\Controllers\V1\InvoiceReturn;

use Illuminate\Http\Request;
use Crater\Http\Requests;
use Crater\Models\InvoiceReturn;
use Crater\Http\Controllers\Controller;
use Crater\Http\Requests\DeleteInvoiceReturnRequest;
use Crater\Jobs\GenerateInvoiceReturnPdfJob;

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
            ->join('users', 'users.id', '=', 'invoice_returns.user_id')
            ->applyFilters($request->only([
                'status',
                'paid_status',
                'customer_id',
                'invoice_id',
                'invoice_return_number',
                'from_date',
                'to_date',
                'orderByField',
                'orderBy',
                'search',
            ]))
            ->whereCompany($request->header('company'))
            ->select('invoice_returns.*', 'users.name')
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
    public function store(Requests\InvoiceReturnsRequest $request)
    {
        $invoice_return = InvoiceReturn::createInvoiceReturn($request);

        if ($request->has('invoiceSend')) {
            $invoice_return->send($request->subject, $request->body);
        }

        GenerateInvoiceReturnPdfJob::dispatch($invoice_return);

        return response()->json([
            'invoice_return' => $invoice_return
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Crater\Models\InvoiceReturn $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(InvoiceReturn $invoices_return)
    {

        $invoices_return->load([
            'items',
            'items.taxes',
            'user',
            'invoiceTemplate',
            'taxes.taxType',
            'fields.customField'
        ]);

        $siteData = [
            'invoice_return' => $invoices_return,
            'nextInvoiceNumber' => $invoices_return->getInvoiceNumAttribute(),
            'invoicePrefix' => $invoices_return->getInvoicePrefixAttribute(),
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
    public function update(Requests\InvoiceReturnsRequest $request, InvoiceReturn $invoices_return)
    {
        $invoices_return = $$invoices_return->updateInvoice($request);

        GenerateInvoiceReturnPdfJob::dispatch($invoices_return, true);

        return response()->json([
            'invoice_return' => $invoices_return,
            'success' => true
        ]);
    }

    /**
     * delete the specified resources in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteInvoiceReturnRequest $request)
    {
        InvoiceReturn::destroy($request->ids);

        return response()->json([
            'success' => true
        ]);
    }
}
