<?php

namespace bizmatesinc\SalesForce;

use bizmatesinc\SalesForce\Exception\BrokenMultipartRecordSet;
use bizmatesinc\SalesForce\Exception\SalesForceException as SalesForceException;
use bizmatesinc\SalesForce\Exception\UnexpectedJsonFormat;
use GuzzleHttp\Client;
use JsonSchema\Validator;

class CRUD
{
    /** @var Client */
    protected $client;

    /** @var API */
    protected $api;

    /** @var string */
    protected $baseUrl;

    /** @var array */
    protected $authHeaders;

    /**
     * CRUD constructor.
     * @param Client $client
     * @param API $api
     * @throws Exception\ApiNotInitialized
     */
    public function __construct(Client $client, API $api)
    {
        $this->client = $client;
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
     * @param string $url
     * @param array $options
     * @return mixed
     * @throws UnexpectedJsonFormat
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getRawResultSet(string $url, array $options)
    {
        $rawResponse = $this->client->request('GET', $url, $options);
        $response = json_decode($rawResponse->getBody(), true);
        $responseObject = json_decode($rawResponse->getBody());

        $schemaObject = json_decode('
        {
            "type": "object",
            "required": ["totalSize", "done", "records"],
            "properties": {
                "totalSize": {
                    "type": "integer"
                }, 
                "done": {
                    "type": "boolean"
                },
                "records": {
                    "type": "array",
                    "items": {
                        "type": "object",
                        "required": ["attributes"],
                        "properties": {
                            "attributes": {
                                "type": "object",
                                "required": ["type", "url"],
                                "properties": {
                                    "type": {
                                        "type": "string"
                                    },
                                    "url": {
                                        "type": "string"
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }');

        $validator = new Validator();
        $validator->validate($responseObject, $schemaObject);
        if (!$validator->isValid()) {
            throw new UnexpectedJsonFormat();
        }

        return $response;
    }

    /**
     * @param $query
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws UnexpectedJsonFormat
     * @throws BrokenMultipartRecordSet
     */
    public function query($query)
    {
        $response = $this->getRawResultSet($this->baseUrl . '/query', [
            'headers' => $this->authHeaders,
            'query' => [
                'q' => $query
            ]
        ]);

        $resultSet = $response['records'] ?? [];

        while (!$response['done']) {
            if (empty($response['nextRecordsUrl'])) {
                throw new BrokenMultipartRecordSet();
            }

            $response = $this->getRawResultSet(
                $this->api->getAuth()->getInstanceUrl() . $response['nextRecordsUrl'],
                [
                    'headers' => $this->authHeaders
                ]
            );

            $resultSet = array_merge($resultSet, $response['records']);
        }

        return $resultSet;
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

        $request = $this->client->request('POST', $url, [
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

        $request = $this->client->request('PATCH', $url, [
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

        $request = $this->client->request('PATCH', $url, [
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

        $request = $this->client->request('DELETE', $url, [
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
