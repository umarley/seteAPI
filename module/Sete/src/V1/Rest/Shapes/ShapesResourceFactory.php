<?php
namespace Sete\V1\Rest\Shapes;

class ShapesResourceFactory
{
    public function __invoke($services)
    {
        return new ShapesResource();
    }
}
