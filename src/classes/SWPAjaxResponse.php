<?php

class SWPAjaxResponse
{
    public $serialized = '';
    public $message = '';
    public $success = false;
    public $data = false;

    public function json()
    {
        return json_encode($this);
    }
}
