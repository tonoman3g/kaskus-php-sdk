<?php

namespace Kaskus;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Kaskus\Exceptions\KaskusClientException;
use Kaskus\Exceptions\KaskusServerException;
use Kaskus\Exceptions\ResourceNotFoundException;
use Kaskus\Exceptions\UnauthorizedException;

class KaskusClient extends \GuzzleHttp\Client
{

    const BASE_URL = 'https://www.kaskus.co.id/api/oauth/';

    public function __construct($consumerKey, $consumerSecret)
    {
        $config = array(
            'base_url' => self::BASE_URL,
            'defaults' => array(
                'auth' => 'oauth',
                'headers' => array(
                    'Return-Type' => 'text/json'
                )
            )
        );
        parent::__construct($config);

        $oauthConfig = array(
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret
        );

        $oauthSubsriber = new Oauth1($oauthConfig);
        $this->getEmitter()->attach($oauthSubsriber);
    }

    public function send(RequestInterface $request)
    {
        try {
            return parent::send($request);
        } catch (RequestException $e) {
            $this->handleException($e);
        }

    }

    protected function handleException(RequestException $exception)
    {
        $response = $exception->getResponse();
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            throw new KaskusServerException();
        }

        try {
            $error = $response->json();
        } catch (\RuntimeException $e) {
            throw new KaskusServerException();
        }

        if (isset($error['errormessage'])) {
            $errorMessage = $error['errormessage'];

            if ($statusCode === 401) {
                throw new UnauthorizedException($errorMessage);
            } elseif ($statusCode === 404) {
                throw new ResourceNotFoundException();
            }
            throw new KaskusClientException($errorMessage);
        }

        throw new KaskusServerException();
    }


}