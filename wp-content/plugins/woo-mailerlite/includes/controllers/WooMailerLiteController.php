<?php

class WooMailerLiteController
{
    public static $instance;

    public $request = [];

    public $loader;

    public $validated = [];

    public static function instance($params = [])
    {
        $class = static::class;
        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new static();
        }
        static::$instance[$class]->request = $_REQUEST;
        static::$instance[$class]->loader = new WooMailerLiteLoader();
        return static::$instance[$class];
    }

    /**
     * @return $this
     */
    protected function authorize()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(['success' => false,  'error' => true, 'message' => 'Unauthorized'], 403);
        }
        
        $this->validate('nonce');

        return $this;
    }

    public function validate($validations)
    {
        try {
            if ($validations === 'nonce' || (is_array($validations) && in_array('nonce', $validations))) {
                check_ajax_referer('woo_mailerlite_admin', 'nonce');
            }

            if (empty($this->request)) {
                $this->request = $_REQUEST;
            }

            $skipSometimes = false;
            $keysToUnset = [];

            if (is_array($validations)) {
                foreach ($validations as $key => $validation) {

                    if (is_array($validation)) {
                        if ($skipSometimes === $key) {
                            continue;
                        }

                        if (strpos($key, '.') !== false) {
                            $needle = explode('.', $key);
                            if (!is_array($this->request[$needle[0]])) {
                                $this->sanitizeRequestKey($needle[0]);
                            }

                            if (isset($this->request[$needle[0]][$needle[1]])) {
                                $this->request[$key] = $this->request[$needle[0]][$needle[1]];
                                $keysToUnset[] = $key;
                            }
                        }

                        if (in_array('required', $validation) && empty($this->request[$key])) {
                            throw new Exception("The $key field is required.");
                        }

                        if (in_array('sometimes', $validation)) {
                            $skipSometimes = $key;
                            if (isset($this->request[$key]) && !empty($this->request[$key])) {
                                $this->validated[$key] = $this->request[$key];
                            }
                            continue;
                        }

                        if (in_array('string', $validation) && !is_string($this->request[$key])) {
                            throw new Exception("The $key field must be a string.");
                        }

                        if (in_array('int', $validation) && !ctype_digit(strval($this->request[$key]))) {
                            throw new Exception("The $key field must be an integer.");
                        }

                        if (in_array('bool', $validation) && !is_bool(filter_var($this->request[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))) {
                            throw new Exception("The $key field must be a boolean.");
                        }
                    }

                    if (isset($this->request[$key])) {
                        $this->validated[$key] = $this->request[$key];
                    }
                }
            }

            foreach ($keysToUnset as $key) {
                unset($this->request[$key]);
            }

            return true;
        } catch (Exception $e) {
            wp_send_json([
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function request($key = null)
    {
        if (strpos($key, '.') !== false) {
            $needle = explode('.', $key);
            return $this->request[$needle[0]][$needle[1]] ?? false;
        }
        if ($key) {
            return $_POST[$key] ?? false;
        }
        return $_POST;
    }

    public function requestHas($key)
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    public function response($response, $status, $message = '')
    {
        if ($response instanceof WooMailerLiteApiResponse) {
            $response->message = $message;
            return wp_send_json($response, $status);
        }
        if (isset($response['message'])) {
            $response['message'] = $message;
        }

        if (is_array($response) && (isset($response['success']) || isset($status))) {
            $response['success'] = false;
            if ($status == 200) {
                $response['success'] = true;
            }
        }
        return wp_send_json($response, $status);
    }

    public function apiClient($apiKey = "")
    {
        return WooMailerLiteApi::client($apiKey);
    }

    public function resolveResource($model, $id)
    {
        return $model::where('id', $id)->first();
    }

    public function sanitizeRequestKey($key)
    {
        $this->request[$key] = json_decode($this->request[$key], true);
    }

    public function isJson($array, $key)
    {
        if (!isset($array[$key]) || !is_string($array[$key])) {
            return false;
        }

        $decoded = json_decode($array[$key], true);
        return json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded));
    }

    public function validateAfterMerge($key, $newKey)
    {
        $this->request[$newKey] = $this->request[$key];
    }
}
