<?php

namespace Slim\Exception;

class ValidationException extends \Exception
{
    private $data = array();
	public $httpcode = 400;
    
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
