<?php
namespace Sete\V1\Rest\Registro;

class RegistroResourceFactory
{
    public function __invoke($services)
    {
        return new RegistroResource();
    }
}
