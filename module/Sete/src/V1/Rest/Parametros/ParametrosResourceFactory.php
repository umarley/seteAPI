<?php
namespace Sete\V1\Rest\Parametros;

class ParametrosResourceFactory
{
    public function __invoke($services)
    {
        return new ParametrosResource();
    }
}
