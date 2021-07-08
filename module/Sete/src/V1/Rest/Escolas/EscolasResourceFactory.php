<?php
namespace Sete\V1\Rest\Escolas;

class EscolasResourceFactory
{
    public function __invoke($services)
    {
        return new EscolasResource();
    }
}
