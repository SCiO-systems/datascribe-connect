<?php

namespace App\Http\Services\SCIO;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Log;

class VocabulariesService
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
     * Fetch a single vocabulary using it's UUID.
     *
     * @param string $uuid
     * @return mixed
     */
    public function getVocabulary($uuid)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/vocabulary/${uuid}")
            ->throw();

        return $response->json('vocabularies.0');
    }

    /**
     * Fetch multiple vocabularies using their UUIDs.
     *
     * @param array $uuids
     * @return mixed
     */
    public function getVocabularies($uuids = [])
    {
        if (empty($uuids)) {
            return ['vocabularies' => []];
        }

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/vocabulary/" . implode(',', $uuids))
            ->throw();

        return $response->json();
    }

    /**
     * Create a new vocabulary.
     *
     * @param mixed $data
     * @return mixed
     */
    public function createVocabulary($data)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->post($this->url . '/vocabulary', $data)
            ->throw();

        return $response->json();
    }

    /**
     * Update an existing vocabulary based on it's UUID.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function updateVocabulary($uuid, $data)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->put($this->url . "/vocabulary/${uuid}", $data)
            ->throw();

        return $response->json();
    }

    /**
     * Delete an existing vocabulary.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function deleteVocabulary($uuid)
    {
        Log::info("Sending delete vocabulary request with UUID: $uuid.");

        try {
            $response = Http::timeout($this->requestTimeout)
                ->withToken($this->token)
                ->asJson()
                ->delete($this->url . "/vocabulary/${uuid}")
                ->throw();
        } catch (RequestException $ex) {
            if ($ex->getCode() === 404) {
                Log::info('Resource with uuid ' . $uuid . ' was not found on remote system, deleting...');
                return true;
            }
        }

        return $response->json();
    }

    /*
     * Search for global vocabularies.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function searchVocabularies($term)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/search/vocabulary/${term}")
            ->throw();

        return $response->json();
    }

    /**
     * Clone an existing vocabulary.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function cloneVocabulary($uuid)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->post($this->url . "/vocabulary/clone", ['uuid' => $uuid])
            ->throw();

        return $response->json();
    }
}
