<?php
namespace Sete\V1\Rest\Graficos;

class GraficosModel
{
    
    
    public function getDadosAlunos($codigoCidade){
        $dbSetePGRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        $arDados['alunos_sem_rota'] = $dbSetePGRotaAtendeAluno->getQtdAlunosSemRota($codigoCidade);
        $arDados['alunos_com_rota'] = $dbSetePGRotaAtendeAluno->getQtdAlunosComRota($codigoCidade);
        return $arDados;
    }
    
    public function getDadosEscolas($codigoCidade){
        
    }
}
