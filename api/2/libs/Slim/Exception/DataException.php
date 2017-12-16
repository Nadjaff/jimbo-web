<?php

namespace Slim\Exception;

class DataException extends Slim\Exception
{
    private $data = array();
	private $httpcode = 500;
    
    public function getData()
    {
        return $this->data;
    }
    public function __construct($message, $data = array())
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }
}
