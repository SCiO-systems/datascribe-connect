<?php

namespace App\Http\Controllers\API\v1\Integrations;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\SCIO\AgronomicDatesService;
use App\Http\Services\SCIO\CropsService;
use App\Http\Services\SCIO\UnitsService;
use App\Http\Services\SCIO\LanguagesService;
use App\Http\Services\SCIO\QuestionsService;
use App\Http\Services\SCIO\OntologiesService;
use App\Http\Services\SCIO\ExtractKeywordsService;
use App\Http\Services\SCIO\PreviewService;
use App\Http\Services\SCIO\SemanticSuggestionsService;
use App\Models\Questionnaire;

class ScioController extends Controller
{
    public function getLanguages()
    {
        return (new LanguagesService())->listLanguages();
    }

    public function getOntologies()
    {
        return (new OntologiesService())->listOntologies();
    }

    public function getSemanticSuggestions(Request $request)
    {
        $term = $request->term ?? '';

        return (new SemanticSuggestionsService())->getSuggestions($term);
    }

    public function getUnits(Request $request)
    {
        $term = $request->term ?? '';

        return (new UnitsService())->getUnits($term);
    }

    public function getExtractedKeywords(Request $request)
    {
        $sentence = $request->sentence ?? '';

        return (new ExtractKeywordsService())->getExtractedKeywords($sentence);
    }

    public function searchQuestions(Request $request)
    {
        $search = $request->search ?? '';

        return (new QuestionsService())->searchQuestions($search);
    }

    public function getCrops(Request $request)
    {
        $term = $request->term ?? '';

        return (new CropsService())->getCrops($term);
    }

    public function getAgronomicDates()
    {
        return (new AgronomicDatesService())->getAgronomicDates();
    }
  
    public function getPreviewLink(Questionnaire $questionnaire)
    {
        return (new PreviewService())->getPreviewLink($questionnaire);
    }

}
