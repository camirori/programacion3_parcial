<?php

class Response{
    public $status;
    public $data;           //va a contener un json

    public function __construct(){
        $this->status='fail';
        $this->data='no data';
    }

}

/*
success	All went well, and (usually) some data was returned.	status, data	
fail	There was a problem with the data submitted, or some pre-condition of the API call wasn't satisfied	status, data	
error	An error occurred in processing the request, i.e. an exception was thrown	status, message
*/