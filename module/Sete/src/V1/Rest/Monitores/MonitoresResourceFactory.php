<?php
namespace Sete\V1\Rest\Monitores;

class MonitoresResourceFactory
{
    public function __invoke($services)
    {
        return new MonitoresResource();
    }
}
