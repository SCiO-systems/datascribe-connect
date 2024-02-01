<?php

namespace App\Http\Services\SCIO;

use Illuminate\Support\Facades\Http;

class LanguagesService
{
    protected $url;
    protected $requestTimeout;
    protected $token;

    public function __construct()
    {
        $this->url = env('SCIO_QUESTIONNAIRE_SERVICE_URL');
        $this->requestTimeout = env('REQUEST_TIMEOUT_SECONDS');
        $this->token = (new AuthService)->getAuthToken();
    }

    /**
     * Fetch the list of languages.
     *
     * @return mixed
     */
    public function listLanguages()
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/languages")
            ->throw();

        return $response->json();
    }
}
