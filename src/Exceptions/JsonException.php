<?php


namespace sobernt\JsonObject\Exceptions;


use Exception;
use Throwable;

class JsonException extends Exception
{
public function __construct($message = "", $code = 0, Throwable $previous = null)
{
    if($code==0) $code=400;
    parent::__construct($message, $code, $previous);
}
}