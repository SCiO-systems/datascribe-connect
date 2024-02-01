<?php

namespace App\Http\Services\SCIO;

use Illuminate\Support\Facades\Http;

class AgronomicDatesService
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
    public function getAgronomicDates(): mixed
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/agronomicdates")
            ->throw();

        return $response->json();
    }
}
