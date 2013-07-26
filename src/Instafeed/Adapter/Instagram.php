<?php
namespace Instafeed\Adapter;

use \Instafeed\Adapter\Instagram\Exception;

class Instagram
{
    /**
     * Get method
     *
     * @var int
     */
    const METHOD_GET = 1;

    /**
     * Post method
     *
     * @var int
     */
    const METHOD_POST = 2;

    /**
     * User agent for our client
     *
     * @var string
     */
    const USER_AGENT = 'Instafeed v1.0.0';

    /**
     * Uri to API implementation
     *
     * @var string
     */
    protected $serverUri = 'https://api.instagram.com';

    /**
     * Dynamic API resource (end-point)
     *
     * @var string
     */
    protected $resource;

    /**
     * Generated JSON data we send off to API
     *
     * @var string
     */
    protected $request;

    /**
     * Stored response we received back from API
     *
     * @var string
     */
    protected $response;

    /**
     * Stored response code we received back from API
     *
     * @var int
     */
    protected $responseCode;

    /**
     * Debugging flag
     *
     * @var bool
     */
    protected $enableDebug = false;

    /**
     * Redirect URI (call-back)
     *
     * @var string
     */
    protected $redirectUri;

    /**
     * API client identifier obtained from Instagram (client_id)
     *
     * @var string
     */
    protected $identifier;

    /**
     * API client secret obtained from Instagram
     *
     * @var string
     */
    protected $secret;

    /**
     * Token obtained during exchange
     *
     * @var string
     */
    protected $token;

    /**
     * Class constructor
     *
     * @param string $identifier
     * @param string $secret
     */
    public function __construct($identifier = null, $secret = null)
    {
        if (!$identifier || !$secret) {
            throw new Exception(
                'You must pass a valid client identifier and secret.'
            );
        }

        $this->identifier = $identifier;
        $this->secret = $secret;
    }

    /**
     * Construct authorization URI
     *
     * @return string
     */
    public function authorizeUri()
    {
        return $this->serverUri .
        '/oauth/authorize/?client_id=' . $this->identifier .
        '&redirect_uri=' . $this->redirectUri() .
        '&response_type=code&scope=basic';
    }

    /**
     * Performs cURL requests (POST,PUT,DELETE,GET) required by API.
     *
     * @throws Exception
     * @param string $method HTTP method that will be used for current request
     */
    protected function request($method = self::METHOD_GET)
    {
        $curl = curl_init();

        switch ($method) {
            case self::METHOD_POST:
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $this->request);
                break;
            default:
                $this->resource .= $this->urlEncode($this->request);
        }

        if ($this->hasToken()) {
            $this->resource .= '&access_token=' . $this->token;
        }

        curl_setopt($curl, CURLOPT_URL, $this->serverUri . $this->resource);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        // If debug is enabled we will output CURL data to screen
        if ($this->enableDebug) {
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
        }

        $this->response = json_decode(curl_exec($curl));

        // Also for debugging purposes pipe request URI and response to
        // error log
        if ($this->enableDebug) {
            var_dump($this->serverUri . $this->resource);
            var_dump($this->response);
        }

        if (curl_errno($curl) > 0) {
            throw new Exception(
                'Unable to process this request. cURL error: ' . curl_errno($curl)
            );
        }

        // Retrieve returned HTTP code and throw exceptions when possible
        // error occurs
        $curlInfo = curl_getinfo($curl);

        if (!empty($curlInfo['http_code'])) {
            $this->responseCode = (int)$curlInfo['http_code'];

            if ($this->responseCode == '400') {
                throw new Exception(
                    'The authorization code provided is no longer valid'
                );
            }
        }

        curl_close($curl);
    }

    /**
     * Retrieves authentication token based on code returned from Instagram
     *
     * @return string|bool
     */
    public function token($code)
    {
        $this->resource = '/oauth/access_token/';
        $this->request = array(
            'client_id' => $this->identifier,
            'client_secret' => $this->secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri(),
            'code' => $code
        );
        $this->request(self::METHOD_POST);

        if ($this->responseCode == 200) {
            return $this->response->access_token;
        }

        return false;
    }

    /**
     * Take array of parameters and convert them to
     * url encoded string.
     *
     * @param string[] $parameters
     * @return string
     */
    protected function urlEncode(array $parameters = null)
    {
        $uri = null;

        if ($parameters) {
            foreach ($parameters as $parameter => $value) {
                $uri .= '&' . $parameter . '=' . urlencode($value);
            }
        }

        return '?' . ltrim($uri, '&');
    }

    /**
     * Enables debugging output
     */
    public function enableDebug()
    {
        $this->enableDebug = true;
    }

    /**
     * Disable debugging output
     */
    public function disableDebug()
    {
        $this->enableDebug = false;
    }

    /**
     * Set access token
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = (string)$token;
    }

    /**
     * Check if access token is set
     *
     * @return bool
     */
    public function hasToken()
    {
        return !empty($this->token);
    }

    /**
     * Set redirect URI
     *
     * @param string $uri
     */
    public function setRedirectUri($uri)
    {
        $this->redirectUri = (string)$uri;
    }

    /**
     * Retrieve redirect URI
     *
     * @throws Exception
     * @return string
     */
    public function redirectUri()
    {
        if (!$this->redirectUri) {
            throw new Exception('Redirect URI is not set, please set it.');
        }

        return $this->redirectUri;
    }
}
