<?php

namespace App\Http\Controllers\API\v1\OAuth;

use Http;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Str;

class Auth0Controller extends Controller
{

    /**
     * The Auth0 base URI. Can be used to change from sandbox mode to production and vice-versa.
     */
    protected $baseURI;

    /**
     * The provided Auth0 client ID.
     */
    protected $clientID;

    /**
     * The provided Auth0 client secret.
     */
    protected $clientSecret;

    /**
     * The Auth0 redirect URI where the authorization code will be sent.
     */
    protected $redirectURI;

    /**
     * Where to redirect after successful login.
     */
    protected $redirectTo;

    /**
     * An opaque value for preventing CSRF attacks.
     */
    protected $state;

    public function __construct()
    {
        $this->baseURI = env('AUTH0_BASE_URI');
        $this->clientID = env('AUTH0_CLIENT_ID');
        $this->clientSecret = env('AUTH0_CLIENT_SECRET');
        $this->redirectURI = env('AUTH0_REDIRECT_URI');
        $this->redirectTo = env('APP_LOGIN_URL');
        $this->requestTimeout = env('REQUEST_TIMEOUT_SECONDS');
        $this->state = hash('sha256', $this->clientID . $this->clientSecret);
    }

    public function redirect()
    {
        $to = "$this->baseURI/authorize?client_id=$this->clientID&response_type=code&redirect_uri=$this->redirectURI&scope=openid profile email&state=$this->state";

        return redirect($to);
    }

    public function callback(Request $request)
    {
        $authorizationCode = $request->code;

        if (strcasecmp($request->state, $this->state) !== 0) {
            return response()->json(['errors' => [
                'error' => 'Failed to match the provided state, possible CSRF attack detected.'
            ]], 400);
        }

        // Get an access token based on the authorization code.
        $response = Http::timeout($this->requestTimeout)
            ->retry(5, 500)
            ->asForm()
            ->acceptJson()
            ->post(
                "$this->baseURI/oauth/token",
                array(
                    'client_id' => $this->clientID,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $authorizationCode,
                    'redirect_uri' => $this->redirectURI,
                )
            );

        if ($response->failed()) {
            return response()->json(['errors' => [
                'error' => 'Authenticating with Auth0 failed.'
            ]], 400);
        }

        // The response as an array from JSON.
        $json = $response->json();

        // The access token to use for fetching profile information.
        $accessToken = $json['access_token'];

        // Support the token expiration TTL of the IDP when generating tokens.
        $expiresIn = $json['expires_in'];

        // Get the user information.
        $response = Http::timeout($this->requestTimeout)
            ->retry(5, 500)
            ->asForm()
            ->acceptJson()
            ->get("$this->baseURI/userinfo", array('access_token' => $accessToken));

        if ($response->failed()) {
            return response()->json(['errors' => [
                'error' => 'Failed to get user profile from Auth0.'
            ]], 400);
        }

        // The actual user profile information.
        $userInfo = $response->json();

        // IDP details.
        $idp = User::IDENTITY_PROVIDER_AUTH0;
        $idpIdentifier = $userInfo['sub'];

        // Check for an existing user.
        $user = User::where('identity_provider', $idp)
            ->where('identity_provider_external_id', $idpIdentifier)
            ->first();

        // Create the user if the user does not exist.
        if (!$user) {
            $user = User::create([
                'email' => $userInfo['email'] ?? '',
                'firstname' => $userInfo['given_name'] ?? '',
                'lastname' => $userInfo['family_name'] ?? '',
                'identity_provider' => $idp,
                'identity_provider_external_id' => $idpIdentifier,
            ]);
        }

        // Get a valid access token for the user.
        $token = JWTAuth::customClaims(['exp' => Carbon::now()->addSeconds($expiresIn)->timestamp])
            ->fromUser($user);

        // The redirect address that contains the url query string param "access_token".
        $to = $this->redirectTo . '?' . http_build_query(['access_token' => $token]);

        return redirect($to);
    }
}
