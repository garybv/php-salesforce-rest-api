<?php

namespace bizmatesinc\SalesForce\Authentication;

interface AuthenticationInterface
{
    public function getAccessToken(): ?string;

    public function getInstanceUrl(): ?string;

    public function getAuthHeaders(): array;
}
