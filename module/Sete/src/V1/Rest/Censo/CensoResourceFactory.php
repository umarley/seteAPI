<?php
namespace Sete\V1\Rest\Censo;

class CensoResourceFactory
{
    public function __invoke($services)
    {
        return new CensoResource();
    }
}
