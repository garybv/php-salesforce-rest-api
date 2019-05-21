<?php

namespace sb_bizmates\SalesForce\Authentication;

interface AuthenticationInterface
{
    public function getAccessToken(): ?string;

    public function getInstanceUrl(): ?string;
}
