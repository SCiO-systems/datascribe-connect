<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemanticTerms\CreateTermRequest;
use App\Http\Services\SCIO\SemanticSuggestionsService;

class TermsController extends Controller
{

    protected $termsService;

    public function __construct()
    {
        $this->termsService = new SemanticSuggestionsService();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTermRequest $request)
    {
        return $this->termsService->createSemanticTerm($request->all());
    }

    public function categories()
    {
        return $this->termsService->getCategories();
    }
}
