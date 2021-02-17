<?php
namespace Sete\V1\Rest\User;

class UserResourceFactory
{
    public function __invoke($services)
    {
        return new UserResource();
    }
}
