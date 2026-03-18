<?php

class WooMailerLiteApiResponse
{
    public $status = 200;
    public $success = true;
    public $data = null;
    public $message = '';
    public $links = null;
    public function __construct($response, $status = null)
    {
        $this->generateResponse($response, $status);
    }

    public function generateResponse($response)
    {
        $this->status = wp_remote_retrieve_response_code($response);
        $this->data = json_decode(wp_remote_retrieve_body($response)) ?? null;
        if (isset($this->data->links)) {
            $this->links = $this->data->links;
        }
        if (isset($this->data->data)) {
            $this->data = $this->data->data;
        }
        if (isset($this->data->errors)) {
            $this->success = false;
            $this->status = 400;
            $this->message = $this->data->errors;
        }
        if (isset($response->errors) || $this->status >= 400) {
            $this->success = false;
            $this->data = $response->errors ?? $this->data;
        }
    }

    public function setResponse($response, $status = 200)
    {
        $this->data = $response;
        $this->status = $status;
        return $this;
    }

    public function addData($key, $value)
    {
        if (isset($this->data)) {
            $this->data->$key = $value;
        }
        return $this;
    }
}