<?php
namespace Sete\V1\Rest\Fornecedores;

class FornecedoresResourceFactory
{
    public function __invoke($services)
    {
        return new FornecedoresResource();
    }
}
