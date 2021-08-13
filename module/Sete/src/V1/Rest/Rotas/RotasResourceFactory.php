<?php
namespace Sete\V1\Rest\Rotas;

class RotasResourceFactory
{
    public function __invoke($services)
    {
        return new RotasResource();
    }
}
