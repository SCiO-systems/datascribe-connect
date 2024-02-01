<?php

namespace App\Http\Services\SCIO;

use Cache;
use Illuminate\Support\Facades\Http;

class AuthService
{
    protected $url;
    protected $authUrl;
    protected $requestTimeout;
    protected $clientID;
    protected $clientSecret;
    protected $audience;
    protected $grantType;
    protected $tokenCacheKey;

    // The time difference between the current time and the time the token was issued.
    public const TOKEN_CACHE_TIME_DIFF_SECONDS = 60;

    public function __construct()
    {
        $this->authUrl = env('SCIO_QUESTIONNAIRE_SERVICE_AUTH_URL');
        $this->requestTimeout = env('REQUEST_TIMEOUT_SECONDS');
        $this->clientID = env('SCIO_QUESTIONNAIRE_CLIENT_ID');
        $this->clientSecret = env('SCIO_QUESTIONNAIRE_CLIENT_SECRET');
        $this->audience = env('SCIO_QUESTIONNAIRE_AUDIENCE');
        $this->grantType = env('SCIO_QUESTIONNAIRE_GRANT_TYPE');
        $this->tokenCacheKey = 'scio_token_agrofims_api';
    }

    /**
     * Get an auth token from the SCIO Auth API and store it in the cache.
     *
     * @param bool $cache Whether to cache the token or not.
     * @return string
     */
    public function getAuthToken(bool $cache = true): string
    {
        if (Cache::has($this->tokenCacheKey)) {
            return Cache::get($this->tokenCacheKey);
        }

        $response = Http::timeout($this->requestTimeout)
            ->post($this->authUrl, [
                'client_id' => $this->clientID,
                'client_secret' => $this->clientSecret,
                'audience' => $this->audience,
                'grant_type' => $this->grantType
            ])->throw();

        $accessToken = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in');

        if ($cache) {
            Cache::put(
                $this->tokenCacheKey,
                $accessToken,
                $expiresIn - self::TOKEN_CACHE_TIME_DIFF_SECONDS
            );
        }

        return $accessToken;
    }
}
