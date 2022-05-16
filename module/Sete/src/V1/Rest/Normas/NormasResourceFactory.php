<?php
namespace Sete\V1\Rest\Normas;

class NormasResourceFactory
{
    public function __invoke($services)
    {
        return new NormasResource();
    }
}
