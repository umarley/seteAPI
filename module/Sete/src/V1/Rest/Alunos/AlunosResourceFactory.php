<?php
namespace Sete\V1\Rest\Alunos;

class AlunosResourceFactory
{
    public function __invoke($services)
    {
        return new AlunosResource();
    }
}
