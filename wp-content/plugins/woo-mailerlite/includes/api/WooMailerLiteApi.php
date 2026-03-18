<?php

class WooMailerLiteApi
{
    protected static $instance = null;

    protected $client = null;

    public $apiType = "";

    protected $baseUrl = "";

    protected $timeout = 90;

    protected $apiKey = "";

    protected $apiSetManually = false;

    protected $headers = [];


    const CLASSIC_API = 'classic';
    const REWRITE_API = 'rewrite';

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = WooMailerLiteOptions::get('apiKey', '');
        $this->setApiType();
        $this->setHeaders();
    }

    public function setApiKey($apiKey)
    {
        $this->apiSetManually = true;
        $this->apiKey = $apiKey;
        $this->setApiType();

        $this->setHeaders();
    }

    public static function client($apiKey = "")
    {
        if (static::$instance == null || $apiKey != "") {
            static::$instance = new static("");
            static::$instance->setApiKey($apiKey);
            static::$instance->setApiClient();
        }
        return static::$instance->client;
    }

    public function isRewrite()
    {
        return $this->apiType == self::REWRITE_API;
    }

    public function isClassic()
    {
        return $this->apiType == self::CLASSIC_API;
    }

    public function get($endpoint, $args = [])
    {
        $args['body']       = $args;
        $args['headers']    = $this->headers;
        $args['timeout']    = $this->timeout;
        $args['user-agent'] = $this->userAgent();
        $this->log($endpoint, $args);
        return $this->response(wp_remote_get($this->baseUrl . $endpoint, $args));
    }

    public function post($endpoint, $args = [])
    {
        $params               = [];
        $params['body']       = json_encode($args);
        $params['headers']    = $this->headers;
        $params['timeout']    = $this->timeout;
        $params['user-agent'] = $this->userAgent();
        $this->log($endpoint, $params);
        return $this->response(wp_remote_post($this->baseUrl . $endpoint, $params));
    }

    public function put($endpoint, $args = [])
    {
        $params               = [];
        $params['method']     = 'PUT';
        $params['headers']    = $this->headers;
        $params['body']       = json_encode($args);
        $params['timeout']    = $this->timeout;
        $params['user-agent'] = $this->userAgent();
        $this->log($endpoint, $params);
        return $this->response(wp_remote_post($this->baseUrl . $endpoint, $params));
    }

    public function delete($endpoint, $args = [])
    {
        $params               = [];
        $params['method']     = 'DELETE';
        $params['headers']    = $this->headers;
        $params['body']       = json_encode($args);
        $params['timeout']    = $this->timeout;
        $params['user-agent'] = $this->userAgent();
        $this->log($endpoint, $params);
        return $this->response(wp_remote_post($this->baseUrl . $endpoint, $params));
    }

    public function successResponse()
    {
        return $this->response([]);
    }

    public function response($response)
    {
        return new WooMailerLiteApiResponse($response);
    }

    protected function setApiClient()
    {
        if ($this->apiType == self::CLASSIC_API) {
            $this->client = new WooMailerLiteClassicApi();
        } else {
            $this->client = new WooMailerLiteRewriteApi();
        }
    }

    protected function userAgent()
    {
        global $wp_version;
        return 'MailerLite WooCommerce/' . WOO_MAILERLITE_VERSION . ' (WP/' . $wp_version . ' WOO/' . get_option('woocommerce_version',
                -1) . ')';
    }
    protected function setHeaders()
    {
        if ($this->apiType == self::REWRITE_API) {
            $this->headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'X-Version'     => '2022-11-21',
            ];
        } elseif($this->apiType == self::CLASSIC_API) {
            $this->headers = [
                'X-MailerLite-ApiKey' => $this->apiKey,
                'Content-Type'        => 'application/json',
                'Accept'              => 'application/json'
            ];
        }
    }

    protected function setApiType()
    {
        if ($this->apiKey == "") {
            return false;
        }
        if (strlen($this->apiKey) < 100) {
            $this->apiType = self::CLASSIC_API;
        } else {
            $this->apiType = self::REWRITE_API;
        }
    }

    public static function getApiType()
    {
        $key  = WooMailerLiteOptions::get('apiKey', '');
        return strlen($key) < 100 ? self::CLASSIC_API : self::REWRITE_API;
    }

    protected function log($endpoint, $args)
    {
        if ($this->apiType === self::REWRITE_API && WooMailerLiteOptions::get('debugMode')) {
            $body = $args['body'];
            unset($args['body']);
            $payload = $args;

            $payload['body']['data'] = is_array($body) ? $body : json_decode($body, true);
            $payload['body']['endpoint'] = $endpoint;

            $payload['body']['settings'] = WooMailerLiteOptions::all();
            $payload['body']['settings']['woo_ml_shop_id'] = WooMailerLiteOptions::get('shopId');
            unset($payload['method']);
            $payload['body'] = json_encode($payload['body']);

            wp_remote_post($this->baseUrl . '/integrations/woocommerce/log', $payload);
        }
        return true;
    }
}