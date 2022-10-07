<?php
namespace Sete\V1\Rest\Chamados;

class ChamadosResourceFactory
{
    public function __invoke($services)
    {
        return new ChamadosResource();
    }
}
