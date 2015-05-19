<?php

namespace Kaskus\Exceptions;


class UnauthorizedException extends KaskusClientException
{


    function __construct($message)
    {
        parent::__construct($message);
    }
}