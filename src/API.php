<?php

namespace bizmatesinc\SalesForce;

use GuzzleHttp\Client;
use bizmatesinc\SalesForce\Authentication\AuthenticationInterface;

class API
{
    /** @var AuthenticationInterface */
    protected $auth;

    public function __construct(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
    }

    public function getVersions()
    {
        $url = $this->auth->getInstanceUrl() . '/services/data/';

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Content-type' => 'application/json'
            ]
        ]);

        return json_decode($request->getBody(), true);
    }
}
