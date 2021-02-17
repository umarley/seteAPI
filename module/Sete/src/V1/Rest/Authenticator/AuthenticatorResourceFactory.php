<?php
namespace Sete\V1\Rest\Authenticator;

class AuthenticatorResourceFactory
{
    public function __invoke($services)
    {
        return new AuthenticatorResource();
    }
}
