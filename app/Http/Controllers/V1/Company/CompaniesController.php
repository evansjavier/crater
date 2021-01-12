<?php

namespace Crater\Http\Controllers\V1\Company;

use Crater\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Crater\Http\Requests\CompanyRequest;
use Crater\Models\Company;
use Crater\Models\CompanySetting;

class CompaniesController extends Controller
{
    /**
     * Retrieve a list of existing Items.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $companies = Company::query()
            ->applyFilters(
                $request->only([
                    'phone',
                    'email',
                    'display_name',
                    'orderByField',
                    'orderBy'
                ])
            )
            ->latest()
            ->paginateData($limit);

        $map_companies = function ($value) {
            $value->append('adminFormattedCreatedAt');
            return $value;
        };

        if($limit == 'all'){
            $companies->transform($map_companies);
        }
        else{
            $companies->getCollection()->transform($map_companies);
        }

        return response()->json([
            'companies' => $companies,
        ]);
    }

    
    /**
     * Create a new company
     * @param \Crater\Http\Requests\CompanyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CompanyRequest $request)
    {
        $company = Company::make($request->only(['name', 'nif']));
        $company->unique_hash = str_random(20);
        $company->save();

        $company->address()->updateOrCreate(['company_id' => $company->id], $request->except(['name', 'nif']));

        $defaultInvoiceEmailBody = 'Has recibido una nueva factura de <b>{COMPANY_NAME}</b>.</br>Por favor descárgala usando el siguiente botón:';
        $defaultEstimateEmailBody = 'Has recibido un nuevo presupuesto de <b>{COMPANY_NAME}</b>.</br>Por favor descárgala usando el siguiente botón:';
        $defaultPaymentEmailBody = 'Gracias por el pago.</b></br>Por favor descargue su comprobante usando el siguiente botón:';
        $billingAddressFormat = '<h3>{BILLING_ADDRESS_NAME}</h3><p>{BILLING_ADDRESS_STREET_1}</p><p>{BILLING_ADDRESS_STREET_2}</p><p>{BILLING_CITY}  {BILLING_STATE}</p><p>{BILLING_COUNTRY}  {BILLING_ZIP_CODE}</p><p>{BILLING_PHONE}</p>';
        $shippingAddressFormat = '<h3>{SHIPPING_ADDRESS_NAME}</h3><p>{SHIPPING_ADDRESS_STREET_1}</p><p>{SHIPPING_ADDRESS_STREET_2}</p><p>{SHIPPING_CITY}  {SHIPPING_STATE}</p><p>{SHIPPING_COUNTRY}  {SHIPPING_ZIP_CODE}</p><p>{SHIPPING_PHONE}</p>';
        $companyAddressFormat = '<h3><strong>{COMPANY_NAME}</strong></h3><p>{COMPANY_NIF}</p><p>{COMPANY_ADDRESS_STREET_1}</p><p>{COMPANY_ADDRESS_STREET_2}</p><p>{COMPANY_CITY} {COMPANY_STATE}</p><p>{COMPANY_COUNTRY}  {COMPANY_ZIP_CODE}</p><p>{COMPANY_PHONE}</p>';
        $paymentFromCustomerAddress = '<h3>{BILLING_ADDRESS_NAME}</h3><p>{BILLING_ADDRESS_STREET_1}</p><p>{BILLING_ADDRESS_STREET_2}</p><p>{BILLING_CITY} {BILLING_STATE} {BILLING_ZIP_CODE}</p><p>{BILLING_COUNTRY}</p><p>{BILLING_PHONE}</p>';

        $settings = [
            'invoice_auto_generate' => 'YES',
            'payment_auto_generate' => 'YES',
            'estimate_auto_generate' => 'YES',
            'save_pdf_to_disk' => 'NO',
            'invoice_mail_body' => $defaultInvoiceEmailBody,
            'estimate_mail_body' => $defaultEstimateEmailBody,
            'payment_mail_body' => $defaultPaymentEmailBody,
            'invoice_company_address_format' => $companyAddressFormat,
            'invoice_shipping_address_format' => $shippingAddressFormat,
            'invoice_billing_address_format' => $billingAddressFormat,
            'estimate_company_address_format' => $companyAddressFormat,
            'estimate_shipping_address_format' => $shippingAddressFormat,
            'estimate_billing_address_format' => $billingAddressFormat,
            'payment_company_address_format' => $companyAddressFormat,
            'payment_from_customer_address_format' => $paymentFromCustomerAddress,
            'currency' => 3,
            'time_zone' => 'Europe/Madrid',
            'language' => 'es',
            'fiscal_year' => '1-12',
            'carbon_date_format' => 'd/m/Y',
            'moment_date_format' => 'DD/MM/YYYY',
            'notification_email' => 'noreply@crater.in',
            'notify_invoice_viewed' => 'NO',
            'notify_estimate_viewed' => 'NO',
            'tax_per_item' => 'NO',
            'discount_per_item' => 'NO',
            'invoice_prefix' => 'INV',
            'estimate_prefix' => 'EST',
            'payment_prefix' => 'PAY',
            'payment_auto_generate' => 'YES',
        ];

        CompanySetting::setSettings($settings, $company->id);

        return response()->json([
            'company' => $company,
            'success' => true
        ]);
    }

}
