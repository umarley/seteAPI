<?php
namespace Sete\V1\Rest\Motoristas;

class MotoristasResourceFactory
{
    public function __invoke($services)
    {
        return new MotoristasResource();
    }
}
