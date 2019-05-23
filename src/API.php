<?php

namespace bizmatesinc\SalesForce;

use bizmatesinc\SalesForce\Authentication\AuthenticationInterface;
use bizmatesinc\SalesForce\Exception\ApiNotInitialized;
use bizmatesinc\SalesForce\Exception\UnexpectedJsonFormat;
use GuzzleHttp\Client;

class API
{
    /** @var Client */
    protected $client;

    /** @var AuthenticationInterface */
    protected $auth;

    /** @var array|null */
    protected $versions;

    /** @var string */
    protected $selectedVersion;

    public function __construct(Client $client, AuthenticationInterface $auth)
    {
        $this->client = $client;
        $this->auth = $auth;
    }

    /**
     * @return AuthenticationInterface
     */
    public function getAuth(): AuthenticationInterface
    {
        return $this->auth;
    }

    /**
     * @throws \JsonException
     * @throws UnexpectedJsonFormat
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getVersions()
    {
        $this->versions = null;
        $this->selectedVersion = null;

        $url = $this->auth->getInstanceUrl() . '/services/data/';

        $request = $this->client->request('GET', $url, [
            'headers' => [
                'Content-type' => 'application/json'
            ]
        ]);

        $versions = json_decode($request->getBody(), true, 10);

        if (($errCode = json_last_error()) !== JSON_ERROR_NONE) {
            throw new \JsonException(json_last_error_msg(), $errCode);
        }

        if (empty($versions) || !is_array($versions)) {
            return null;
        }

        $vv = [];

        foreach ($versions as $v) {
            if (empty($v['version']) || empty($v['url'])) {
                throw new UnexpectedJsonFormat();
            }
            $vv[$v['version']] = $v['url'];
        }

        ksort($vv);

        end($vv);

        $this->selectedVersion = key($vv);

        return $this->versions = $vv;
    }

    public function hasVersions()
    {
        return !empty($this->versions) && is_array($this->versions);
    }

    public function versionAvailable($version)
    {
        return $this->hasVersions() && array_key_exists($version, $this->versions);
    }

    public function setVersion($version)
    {
        if ($this->versionAvailable($version)) {
            $this->selectedVersion = $version;
        }
    }

    /**
     * @return string
     * @throws ApiNotInitialized
     */
    public function getBaseUrl(): string
    {
        if (!$this->hasVersions() || empty($this->selectedVersion)) {
            throw new ApiNotInitialized();
        }
        return $this->auth->getInstanceUrl() . $this->versions[$this->selectedVersion];
    }
}
