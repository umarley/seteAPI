<?php
namespace Sete\V1\Rest\Relatorios;

class RelatoriosResourceFactory
{
    public function __invoke($services)
    {
        return new RelatoriosResource();
    }
}
