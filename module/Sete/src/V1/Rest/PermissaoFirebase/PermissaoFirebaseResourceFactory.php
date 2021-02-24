<?php
namespace Sete\V1\Rest\PermissaoFirebase;

class PermissaoFirebaseResourceFactory
{
    public function __invoke($services)
    {
        return new PermissaoFirebaseResource();
    }
}
