<?php

namespace bizmatesinc\SalesForce;

use GuzzleHttp\Client;
use bizmatesinc\SalesForce\Exception\SalesForceException as SalesForceException;

class CRUD
{
    /** @var API */
    protected $api;

    /** @var string */
    protected $baseUrl;

    /** @var array */
    protected $authHeaders;

    /**
     * CRUD constructor.
     * @param API $api
     * @throws Exception\ApiNotInitialized
     */
    public function __construct(API $api)
    {
        $this->api = $api;

        $this->baseUrl = $api->getBaseUrl();
        $this->authHeaders = $this->api->getAuth()->getAuthHeaders();
    }

    /**
     * @param string[] ...$parts
     * @return string
     */
    public function url(...$parts)
    {
        array_unshift($parts, $this->baseUrl);
        return implode('/', $parts);
    }

    /**
     * @param $query
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query($query)
    {
        $url = $this->baseUrl . '/query';

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => $this->authHeaders,
            'query' => [
                'q' => $query
            ]
        ]);

        return json_decode($request->getBody(), true);
    }

    /**
     * @param $object
     * @param array $data
     * @return mixed
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create($object, array $data)
    {
        $url = $this->url('sobjects', $object);

        $client = new Client();

        $request = $client->request('POST', $url, [
            'headers' => $this->authHeaders,
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

    /**
     * @param $object
     * @param $id
     * @param array $data
     * @return int
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update($object, $id, array $data)
    {
        $url = $this->url('sobjects', $object, $id);

        $client = new Client();

        $request = $client->request('PATCH', $url, [
            'headers' => $this->authHeaders,
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

    /**
     * @param $object
     * @param $field
     * @param $id
     * @param array $data
     * @return int
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upsert($object, $field, $id, array $data)
    {
        $url = $this->url('sobjects', $object, $field, $id);

        $client = new Client();

        $request = $client->request('PATCH', $url, [
            'headers' => $this->authHeaders,
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

    /**
     * @param $object
     * @param $id
     * @return bool
     * @throws SalesForceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($object, $id)
    {
        $url = $this->url('sobjects', $object, $id);

        $client = new Client();
        $request = $client->request('DELETE', $url, [
            'headers' => $this->authHeaders,
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
