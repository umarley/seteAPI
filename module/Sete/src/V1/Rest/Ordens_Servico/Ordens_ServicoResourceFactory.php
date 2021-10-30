<?php
namespace Sete\V1\Rest\Ordens_Servico;

class Ordens_ServicoResourceFactory
{
    public function __invoke($services)
    {
        return new Ordens_ServicoResource();
    }
}
