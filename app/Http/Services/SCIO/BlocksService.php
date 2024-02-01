<?php

namespace App\Http\Services\SCIO;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Log;

class BlocksService
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
     * Fetch a single block using it's UUID.
     *
     * @param string $uuid
     * @return mixed
     */
    public function getBlock($uuid)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/block/$uuid")
            ->throw();

        return $response->json('blocks.0');
    }

    /**
     * Fetch multiple blocks using their UUIDs.
     *
     * @param array $uuids
     * @return mixed
     */
    public function getBlocks($uuids = [])
    {
        if (empty($uuids)) {
            return ['blocks' => []];
        }

        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/block/" . implode(',', $uuids))
            ->throw();

        return $response->json();
    }

    /**
     * Create a new block.
     *
     * @param mixed $data
     * @return mixed
     */
    public function createBlock($data)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->post($this->url . "/block", $data)
            ->throw();

        return $response->json();
    }

    /**
     * Update an existing block based on it's UUID.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function updateBlock($uuid, $data)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->put($this->url . "/block/$uuid", $data)
            ->throw();

        return $response->json();
    }

    /**
     * Delete an existing block.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function deleteBlock($uuid)
    {
        Log::info("Sending delete block request with UUID: $uuid.");

        try {
            $response = Http::timeout($this->requestTimeout)
                ->withToken($this->token)
                ->asJson()
                ->delete($this->url . "/block/$uuid")
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
     * Search for global blocks.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function searchBlocks($term)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->get($this->url . "/search/block/$term")
            ->throw();

        return $response->json();
    }

    /**
     * Clone an existing block.
     *
     * @param string $uuid
     * @param mixed $data
     * @return mixed
     */
    public function cloneBlock($uuid)
    {
        $response = Http::timeout($this->requestTimeout)
            ->withToken($this->token)
            ->asJson()
            ->post($this->url . "/block/clone", ['uuid' => $uuid])
            ->throw();

        return $response->json();
    }
}
