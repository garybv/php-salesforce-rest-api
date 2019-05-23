<?php

declare(strict_types=1);

use bizmatesinc\SalesForce\API;
use bizmatesinc\SalesForce\Authentication\PasswordAuthentication;
use bizmatesinc\SalesForce\CRUD;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CRUDTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \bizmatesinc\SalesForce\Exception\ApiNotInitialized
     * @throws \bizmatesinc\SalesForce\Exception\UnexpectedJsonFormat
     */
    public function testQuery(): void
    {
        /** @var MockObject|PasswordAuthentication $auth */
        $auth = $this->createMock(PasswordAuthentication::class);
        $auth->method('getAccessToken')->willReturn('SomeRandomTokenGoesHere');

        /** @var array $authHeaders */
        $authHeaders = $auth->getAuthHeaders();

        /** @var MockObject|API $api */
        $api = $this->createMock(API::class);
        $api->method('getBaseUrl')->willReturn('https://test.my.salesforce.com/services/data/v45.0');
        $api->method('getAuth')->willReturn($auth);

        /** @var string $baseUrl */
        $baseUrl = $api->getBaseUrl();

        $sfQuery = 'SELECT Id, Name FROM Account';

        /** @var MockObject|Client $client */
        $client = $this->createMock(Client::class);
        $client->method('request')->willReturnCallback(function ($method, $uri = '', array $options = []) use ($baseUrl, $authHeaders, $sfQuery) {
            $headers = $options['headers'] ?? null;
            $q = $options['query']['q'] ?? null;

            if ($uri !== $baseUrl . '/query') {
                throw new Exception('Not Found', 404);
            }

            if ($method !== 'GET') {
                throw new Exception('Method Not Allowed', 405);
            }

            if ($headers !== $authHeaders) {
                throw new Exception('Forbidden', 403);
            }

            if ($q === $sfQuery) {
                return new Response(200, ['Content-Type' => 'application/json;charset=UTF-8'], '
                    {
                        "totalSize": 3,
                        "done": true,
                        "records": [
                            {
                                "attributes": {
                                    "type": "Account",
                                    "url": "/services/data/v45.0/sobjects/Account/foo"
                                },
                                "Id": "foo",
                                "Name": "Foo, Ltd."
                            },
                            {
                                "attributes": {
                                    "type": "Account",
                                    "url": "/services/data/v45.0/sobjects/Account/bar"
                                },
                                "Id": "bar",
                                "Name": "Bar, Corp."
                            },
                            {
                                "attributes": {
                                    "type": "Account",
                                    "url": "/services/data/v45.0/sobjects/Account/baz"
                                },
                                "Id": "baz",
                                "Name": "Baz, Inc."
                            }
                        ]
                    }
                ', '1.1');
            }

            throw new Exception('Internal Server Error: query not understood', 500);
        });

        $crud = new CRUD($client, $api);

        $result = $crud->query($sfQuery);

        $this->assertIsArray($result);
    }
}
