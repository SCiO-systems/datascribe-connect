<?php

namespace App\Http\Services\SCIO;

use Illuminate\Support\Facades\Http;

class SemanticSuggestionsService
{
    protected string $url;
    protected int $requestTimeout;
    protected string $token;

    public function __construct()
    {
        $this->url = env('SCIO_QUESTIONNAIRE_SERVICE_URL');
        $this->requestTimeout = env('REQUEST_TIMEOUT_SECONDS');
        $this->token = (new AuthService)->getAuthToken();
    }

    /**
     * Create a new semantic term.
     *
     * @param  [type] $data
     * @return mixed
     */
    public function createSemanticTerm($data): mixed
    {
        if (empty($data)) {
            return response()->status(422);
        }

        $vocabulary = $data['vocabulary'];

        if (!in_array($vocabulary, ['term', 'unit', 'date'])) {
            return response()->status(422);
        }

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->post($this->url . "/submit", $data)
            ->throw();

        return $response->json();
    }

    /**
     * Get semantic suggestions.
     *
     * @param  string $ontology The ontology index.
     * @param  string $term     The term to search for.
     * @return mixed
     */
    public function getSuggestions($term): mixed
    {
        if (empty($term)) {
            return [];
        }

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/terms/${term}")
            ->throw();

        return $response->json();
    }

    /**
     * Get term categories
     *
     * @return mixed
     */
    public function getCategories(): mixed
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/units/categories")
            ->throw();

        return $response->json();
    }
}
