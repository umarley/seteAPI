<?php
namespace Sete\V1\Rest\Veiculos;

class VeiculosResourceFactory
{
    public function __invoke($services)
    {
        return new VeiculosResource();
    }
}
