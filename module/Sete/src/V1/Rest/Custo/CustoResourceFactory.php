<?php
namespace Sete\V1\Rest\Custo;

class CustoResourceFactory
{
    public function __invoke($services)
    {
        return new CustoResource();
    }
}
