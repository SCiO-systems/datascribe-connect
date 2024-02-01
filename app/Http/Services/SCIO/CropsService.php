<?php

namespace App\Http\Services\SCIO;

use Illuminate\Support\Facades\Http;

class CropsService
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
     * Search for units based on term.
     *
     * @param string $term The term to search for.
     * @return mixed
     */
    public function getCrops($term): mixed
    {
        if (empty($term)) {
            return [];
        }

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/crops/${term}")
            ->throw();

        return $response->json();
    }
}
