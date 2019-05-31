<?php

declare(strict_types=1);

use bizmatesinc\SalesForce\API;
use bizmatesinc\SalesForce\Authentication\PasswordAuthentication;
use bizmatesinc\SalesForce\CRUD;
use bizmatesinc\SalesForce\Exception\ApiNotInitialized;
use bizmatesinc\SalesForce\Exception\BrokenMultipartRecordSet;
use bizmatesinc\SalesForce\Exception\UnexpectedJsonFormat;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CRUDTest extends TestCase
{
    const SF_INSTANCE_URL = 'https://test.my.salesforce.com';
    const SF_ACCESS_TOKEN = 'SomeRandomTokenGoesHere';

    /**
     * @return CRUD
     * @throws ReflectionException
     * @throws ApiNotInitialized
     */
    private function makeCRUD($apiRequests)
    {
        /** @var MockObject|PasswordAuthentication $auth */
        $auth = $this->createMock(PasswordAuthentication::class);
        $auth->method('getInstanceUrl')->willReturn(self::SF_INSTANCE_URL);
        $auth->method('getAccessToken')->willReturn(self::SF_ACCESS_TOKEN);

        /** @var array $authHeaders */
        $authHeaders = $auth->getAuthHeaders();

        /** @var MockObject|API $api */
        $api = $this->createMock(API::class);
        $api->method('getBaseUrl')->willReturn(self::SF_INSTANCE_URL . '/services/data/v45.0');
        $api->method('getAuth')->willReturn($auth);

        /** @var string $baseUrl */
        $baseUrl = $api->getBaseUrl();

        /** @var MockObject|Client $client */
        $client = $this->createMock(Client::class);
        $client->method('request')->willReturnCallback(function ($method, $uri = '', array $options = []) use ($baseUrl, $authHeaders, $apiRequests) {
            $headers = $options['headers'] ?? null;
            $q = $options['query']['q'] ?? null;

            if ($headers !== $authHeaders) {
                throw new Exception('Forbidden', 403);
            }

            foreach ($apiRequests as $rq) {
                if ($method === $rq['method'] && $uri === $baseUrl . $rq['endpoint'] && $q === $rq['q']) {
                    return new Response(200, ['Content-Type' => 'application/json;charset=UTF-8'], $rq['response'], '1.1');
                }
            }

            throw new Exception('Internal Server Error', 500);
        });

        return new CRUD($client, $api);
    }

    /**
     * @throws ReflectionException
     * @throws GuzzleException
     * @throws ApiNotInitialized
     * @throws UnexpectedJsonFormat
     * @throws BrokenMultipartRecordSet
     */
    public function testQuery(): void
    {
        $sfQuery = 'SELECT Id, Name FROM Account';

        $crud = $this->makeCRUD([
            [
                'method' => 'GET',
                'endpoint' => '/query',
                'q' => $sfQuery,
                'response' => '
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
                '
            ]
        ]);

        $result = $crud->query($sfQuery);

        // TODO Add more assertions
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals(3, $crud->getLastQueryRecordsCount());
    }

    /**
     * @throws ReflectionException
     * @throws GuzzleException
     * @throws ApiNotInitialized
     * @throws UnexpectedJsonFormat
     * @throws BrokenMultipartRecordSet
     */
    public function testQueryBrokenMultipart(): void
    {
        $sfQuery = 'SELECT Id, Name FROM Account';

        $crud = $this->makeCRUD([
            [
                'method' => 'GET',
                'endpoint' => '/query',
                'q' => $sfQuery,
                'response' => '
                    {
                        "totalSize": 3,
                        "done": false,
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
                '
            ]
        ]);

        $this->expectException(BrokenMultipartRecordSet::class);

        try {
            $crud->query($sfQuery);
        } finally {
            $this->assertNull($crud->getLastQueryRecordsCount());
        }
    }

    /**
     * @throws ReflectionException
     * @throws GuzzleException
     * @throws ApiNotInitialized
     * @throws UnexpectedJsonFormat
     * @throws BrokenMultipartRecordSet
     */
    public function testQueryMultipartOK(): void
    {
        $sfQuery = 'SELECT Id, Name FROM Account';

        $crud = $this->makeCRUD([
            [
                'method' => 'GET',
                'endpoint' => '/query',
                'q' => $sfQuery,
                'response' => '
                    {
                        "totalSize": 4,
                        "done": false,
                        "nextRecordsUrl": "/services/data/v45.0/query/SalesForceNextRecordSetID-2000",
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
                '
            ],
            [
                'method' => 'GET',
                'endpoint' => '/query/SalesForceNextRecordSetID-2000',
                'q' => null,
                'response' => '
                    {
                        "totalSize": 4,
                        "done": true,
                        "records": [
                            {
                                "attributes": {
                                    "type": "Account",
                                    "url": "/services/data/v45.0/sobjects/Account/abc"
                                },
                                "Id": "abc",
                                "Name": "ABC Holdings"
                            }
                        ]
                    }
                '
            ],
        ]);

        $result = $crud->query($sfQuery);

        // TODO Add more assertions
        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals(4, $crud->getLastQueryRecordsCount());
    }
}
