<?php

namespace Crater\Http\Controllers\V1\General;

use Crater\Models\Company;
use Crater\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'companies' => Company::all()
        ]);
    }
}
