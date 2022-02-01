<?php
namespace Sete\V1\Rest\Acesso;

class AcessoResourceFactory
{
    public function __invoke($services)
    {
        return new AcessoResource();
    }
}
