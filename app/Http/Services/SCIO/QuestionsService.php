<?php

namespace App\Http\Services\SCIO;

use Illuminate\Support\Facades\Http;

class QuestionsService
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
     * Extract keywords from a sentence.
     *
     * @param string $sentence The sentence to extract keywords for.
     * @return mixed
     */
    public function searchQuestions($question): mixed
    {
        if (empty($question)) {
            return [];
        }

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/question/search/data_cast_questions/${question}")
            ->throw();

        return $response->json();
    }
}
