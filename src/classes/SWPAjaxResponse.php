<?php

class SWPAjaxResponse
{
    public $serialized = '';
    public $message = '';
    public $success = false;
    public $data = false;

    function json()
    {
        return json_encode($this);
    }
}
