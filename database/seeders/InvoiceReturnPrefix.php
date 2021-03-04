<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Crater\Models\{Company, CompanySetting};

class InvoiceReturnPrefix extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = Company::get();

        foreach ($companies as $company) {
            $prefix = CompanySetting::getSetting(
                'invoice_return_prefix',
                $company->id
            );

            if(!$prefix){

                $settings = [
                    'invoice_return_prefix' => 'RET'
                ];
                CompanySetting::setSettings($settings, $company->id);
            }
        }
    }
}
