<?php

namespace Kaskus\Exceptions;


class ResourceNotFoundException extends KaskusClientException
{


    function __construct()
    {
        parent::__construct('Resource not found');
    }
}