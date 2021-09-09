<?php
namespace Sete\V1\Rest\Garagens;

class GaragensResourceFactory
{
    public function __invoke($services)
    {
        return new GaragensResource();
    }
}
