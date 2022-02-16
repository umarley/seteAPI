<?php
namespace Sete\V1\Rest\Importacao;

class ImportacaoResourceFactory
{
    public function __invoke($services)
    {
        return new ImportacaoResource();
    }
}
