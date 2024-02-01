<?php

namespace App\Http\Services\SCIO;

use Illuminate\Support\Facades\Http;
use App\Models\Questionnaire;

use Log;

class PreviewService
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
     * Prepare the preview for a questionnaire
     *
     * @param Questionnaire $questionnaire The questionnaire to prepare preview for.
     * @return void
     */
    public function preparePreview(Questionnaire $questionnaire): void 
    {
        $uuid = $questionnaire->external_id;
        try {
            Http::timeout($this->requestTimeout)
                ->withToken($this->token)
                ->asJson()
                ->get($this->url . "/preview/" . $uuid)
                ->throw();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Returns the preview link for a questionnaire
     *
     * @param Questionnaire $questionnaire The questionnaire to get the preview link for.
     * @return mixed
     */
    public function getPreviewLink(Questionnaire $questionnaire): mixed
    {
        $uuid = $questionnaire->external_id;
        try {
            $response = Http::timeout($this->requestTimeout)
                ->withToken($this->token)
                ->asJson()
                ->get($this->url . "/preview-link/" . $uuid)
                ->throw();
            return $response->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }
}
