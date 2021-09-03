<?php
namespace Sete\V1\Rest\Localidades;

class LocalidadesResourceFactory
{
    public function __invoke($services)
    {
        return new LocalidadesResource();
    }
}
