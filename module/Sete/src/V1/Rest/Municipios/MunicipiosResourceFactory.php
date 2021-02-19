<?php
namespace Sete\V1\Rest\Municipios;

class MunicipiosResourceFactory
{
    public function __invoke($services)
    {
        return new MunicipiosResource();
    }
}
