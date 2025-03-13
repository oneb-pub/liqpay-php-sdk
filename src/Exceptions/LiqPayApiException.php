<?php

namespace LiqPay\Exceptions;

class LiqPayApiException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, 500);
    }
}