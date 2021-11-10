<?php
namespace Sete\V1\Rest\OrdensServicos;

class OrdensServicosResourceFactory
{
    public function __invoke($services)
    {
        return new OrdensServicosResource();
    }
}
