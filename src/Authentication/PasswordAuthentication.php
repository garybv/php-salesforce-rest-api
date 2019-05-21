<?php

namespace sb_bizmates\SalesForce\Authentication;

use GuzzleHttp\Client;
use sb_bizmates\SalesForce\Exception\SalesForceAuthentication;

class PasswordAuthentication implements AuthenticationInterface
{
    const DEFAULT_ENDPOINT = 'https://login.salesforce.com/';

    /** @var string */
    protected $endpoint = self::DEFAULT_ENDPOINT;

    /** @var Client */
    protected $client;

    /** @var array */
    protected $options;

    /** @var string|null */
    protected $access_token;

    /** @var string|null */
    protected $instance_url;

    public function __construct(Client $client, array $options)
    {
        $this->client = $client;
        $this->options = $options;
    }

    /**
     * @throws SalesForceAuthentication
     */
    public function authenticate()
    {
        $request = $this->client->request('POST', $this->endpoint . 'services/oauth2/token', ['form_params' => $this->options]);

        $response = json_decode($request->getBody(), true);

        if ($response) {
            $this->access_token = $response['access_token'];
            $this->instance_url = $response['instance_url'];

            $_SESSION['salesforce'] = $response;
        } else {
            throw new SalesForceAuthentication($request->getBody());
        }
    }

    /**
     * @param string $endpoint
     * @return $this|self
     */
    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    /**
     * @return string|null
     */
    public function getInstanceUrl(): ?string
    {
        return $this->instance_url;
    }
}
