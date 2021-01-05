<?php

namespace Crater\Http\Controllers\V1\Company;

use Crater\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Crater\Models\Company;

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
        return response()->json([
            'companies' => Company::all(),            
        ]);
    }
}
