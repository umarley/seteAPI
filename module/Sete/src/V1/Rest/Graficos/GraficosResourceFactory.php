<?php
namespace Sete\V1\Rest\Graficos;

class GraficosResourceFactory
{
    public function __invoke($services)
    {
        return new GraficosResource();
    }
}
