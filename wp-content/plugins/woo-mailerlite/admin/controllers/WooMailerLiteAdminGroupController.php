<?php

class WooMailerLiteAdminGroupController extends WooMailerLiteController
{
    public function createGroup()
    {
        $this->authorize()->validate(['group' => ['required', 'string']]);
        $response = $this->apiClient()->createGroup($this->validated['group']);
        return $this->response($response, $response->status);
    }
}