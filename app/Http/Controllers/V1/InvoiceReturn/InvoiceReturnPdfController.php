<?php

namespace Crater\Http\Controllers\V1\InvoiceReturn;

use Crater\Http\Controllers\Controller;
use Crater\Models\InvoiceReturn;

class InvoiceReturnPdfController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(InvoiceReturn $invoice_return)
    {
        return $invoice_return->getGeneratedPDFOrStream('invoice_return');
    }
}
