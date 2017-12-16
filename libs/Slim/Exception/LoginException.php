<?php

namespace Slim\Exception;

class LoginException extends Slim\Exception
{
    private $data = array();
	public $httpcode = 401;
    
    public function getData()
    {
        return $this->data;
    }
    public function __construct($code, $message, $data = array())
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }
}
