<?php

namespace sb_bizmates\SalesForce;

use GuzzleHttp\Client;
use sb_bizmates\SalesForce\Authentication\AuthenticationInterface;
use sb_bizmates\SalesForce\Exception\SalesForce as SalesForceException;

class CRUD
{
    /** @var AuthenticationInterface */
    protected $auth;

    public function __construct(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
    }

    public function query($query)
    {
        $url = $this->auth->getInstanceUrl() . '/services/data/v39.0/query';

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'OAuth ' . $this->auth->getAccessToken(),
                'Content-type' => 'application/json'
            ],
            'query' => [
                'q' => $query
            ]
        ]);

        return json_decode($request->getBody(), true);
    }

    public function create($object, array $data)
    {
        $url = $this->auth->getInstanceUrl() . "/services/data/v39.0/sobjects/{$object}/";

        $client = new Client();

        $request = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'OAuth ' . $this->auth->getAccessToken(),
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 201) {
            throw new SalesForceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        $response = json_decode($request->getBody(), true);
        $id = $response["id"];

        return $id;

    }

    public function update($object, $id, array $data)
    {
        $url = $this->auth->getInstanceUrl() . "/services/data/v39.0/sobjects/{$object}/{$id}";

        $client = new Client();

        $request = $client->request('PATCH', $url, [
            'headers' => [
                'Authorization' => 'OAuth ' . $this->auth->getAccessToken(),
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 204) {
            throw new SalesForceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return $status;
    }

    public function upsert($object, $field, $id, array $data)
    {
        $url = $this->auth->getInstanceUrl() . "/services/data/v39.0/sobjects/{$object}/{$field}/{$id}";

        $client = new Client();

        $request = $client->request('PATCH', $url, [
            'headers' => [
                'Authorization' => 'OAuth ' . $this->auth->getAccessToken(),
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 204 && $status != 201) {
            throw new SalesForceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return $status;
    }

    public function delete($object, $id)
    {
        $url = $this->auth->getInstanceUrl() . "/services/data/v39.0/sobjects/{$object}/{$id}";

        $client = new Client();
        $request = $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => 'OAuth ' . $this->auth->getAccessToken(),
                'Content-type' => 'application/json'
            ],
        ]);

        $status = $request->getStatusCode();

        if ($status != 204) {
            throw new SalesForceException(
                "Error: call to URL {$url} failed with status {$status}, response: {$request->getReasonPhrase()}"
            );
        }

        return true;
    }
}
