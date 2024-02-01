<?php

namespace App\Http\Services\SCIO;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Log;

class QuestionnaireService
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
     * Fetch a single questionnaire by it's UUID.
     *
     * @param string $uuid
     * @return mixed
     */
    public function getQuestionnaire($uuid)
    {
        Log::info("Sending fetch questionnaire request with UUID: $uuid.");

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/questionnaire/${uuid}")
            ->throw();

        return $response->json();
    }

    /**
     * Create a new questionnaire.
     *
     * @param mixed $data
     * @return mixed
     */
    public function createQuestionnaire($data)
    {
        Log::info("Sending create new questionnaire request.");

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->withBody($data, 'application/json')
            ->post($this->url . '/questionnaire')
            ->throw();

        return $response->json();
    }

    /**
     * Update an existing questionnaire based on it's UUID.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function updateQuestionnaire($uuid, $data)
    {
        Log::info("Sending update questionnaire request.");

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->withBody($data, 'application/json')
            ->put($this->url . "/questionnaire/${uuid}")
            ->throw();

        return $response->json();
    }

    /**
     * Delete an existing questionnaire based on it's UUID.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function deleteQuestionnaire($uuid)
    {
        Log::info("Sending delete questionnaire request with UUID: $uuid.");

        try {
            $response = Http::timeout($this->requestTimeout)
                ->withToken($this->token)
                ->asJson()
                ->delete($this->url . "/questionnaire/${uuid}")
                ->throw();
        } catch (RequestException $ex) {
            if ($ex->getCode() === 404) {
                Log::info('Resource with uuid ' . $uuid . ' was not found on remote system, deleting...');
                return true;
            }
        }

        return $response->json();
    }

    /**
     * Download a questionnaire in XLS format based on it's UUID.
     *
     * @param string $uuid
     * @return mixed
     */
    public function downloadQuestionnaire($uuid)
    {
        Log::info("Sending download questionnaire request with UUID: $uuid.");

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->get($this->url . "/transform/${uuid}")
            ->throw();

        return $response;
    }

    /**
     * Clone a questionnaire.
     *
     * @param $uuid
     */
    public function cloneQuestionnaire($uuid)
    {
        Log::info("Sending clone questionnaire request with UUID: $uuid.");

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->post($this->url . "/questionnaire/clone", ['uuid' => $uuid])
            ->throw();

        return $response->json();
    }

    /**
     * Import a questionnaire from file.
     *
     * @param $file
     */
    public function importQuestionnaire($data)
    {
        Log::info("Sending import questionnaire request.");

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->withBody($data, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->post($this->url . "/transform/binary")
            ->throw();

        return $response->json();
    }

    /**
     * Download carob script.
     *
     * @param string $uuid
     * @return mixed
     */
    public function downloadCarobScript($uuid)
    {
        Log::info("Downloading carob script for questionnaire request with UUID: $uuid.");

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->get($this->url . "/carob/${uuid}")
            ->throw();

        return $response;
    }
}
