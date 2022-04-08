<?php
namespace Sete\V1\Rest\Graficos;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class GraficosModel
{
    
    
    public function getDadosAlunos($codigoCidade){
        $dbSetePGRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        $arDados['alunos_sem_rota'] = $dbSetePGRotaAtendeAluno->getQtdAlunosSemRota($codigoCidade);
        $arDados['alunos_com_rota'] = $dbSetePGRotaAtendeAluno->getQtdAlunosComRota($codigoCidade);
        return $arDados;
    }

    public function getDadosEscolas($codigoCidade){
        $dbSetePGEscolas = new \Db\SetePG\SeteEscolas();
        $dbSetePGRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        $arDados['alunos_transportados_escola'] = number_format($dbSetePGRotaAtendeAluno->getQtdAlunosComRota($codigoCidade)/$dbSetePGEscolas->qtdEscolasAtendidas($codigoCidade),2);
        return $arDados;
    }

    public function getDadosRotas($codigoCidade){
        $dbSetePGRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        $dbSetePGRotas = new \Db\SetePG\SeteRotas();
        $arDados['alunos_por_rota'] = number_format($dbSetePGRotaAtendeAluno->getQtdAlunosComRota($codigoCidade)/$dbSetePGRotas->qtdRotas($codigoCidade),2);
        return $arDados;
    }
    
    public function getDadosEscolaridade($codigoCidade){
        $dbSetePGAlunos = new \Db\SetePG\SeteAlunos();
        $arEscolaridades = $dbSetePGAlunos->qtdAlunosEscolaridade($codigoCidade);
        $arLabel = [];
        $arValues = [];
        foreach ($arEscolaridades as $nivel){
            $arLabel[] = $nivel['nm_nivel'];
            $arValues[] = $nivel['qtd'];
        } 
        $arDados['labels'] = $arLabel;
        $arDados['values'] = $arValues;
        return $arDados;
    }

    public function getDadosTurno($codigoCidade){
        $dbSetePGAlunos = new \Db\SetePG\SeteAlunos();
        $arTurnos = $dbSetePGAlunos->qtdAlunosTurno($codigoCidade);
        $arLabel = [];
        $arValues = [];
        foreach ($arTurnos as $nivel){
            $arLabel[] = $nivel['nm_tp_turno'];
            $arValues[] = $nivel['qtd'];

        } 
        $arDados['labels'] = $arLabel;
        $arDados['values'] = $arValues;
        return $arDados;
    }

    public function getDadosResidiencia($codigoCidade){
        $dbSetePGAlunos = new \Db\SetePG\SeteAlunos();
        $arResidencias = $dbSetePGAlunos->qtdAlunosResidencia($codigoCidade);
        $arLabel = [];
        $arValues = [];
        foreach ($arResidencias as $nivel){
            $arLabel[] = $nivel['nm_tp_residencia'];
            $arValues[] = $nivel['qtd'];

        } 
        $arDados['labels'] = $arLabel;
        $arDados['values'] = $arValues;
        return $arDados;
    }

    public function getDadosCor($codigoCidade){
        $dbSetePGAlunos = new \Db\SetePG\SeteAlunos();
        $arCores = $dbSetePGAlunos->qtdAlunosCor($codigoCidade);
        $arLabel = [];
        $arValues = [];
        foreach ($arCores as $nivel){
            $arLabel[] = $nivel['nm_tp_cor'];
            $arValues[] = $nivel['qtd'];
        } 
        $arDados['labels'] = $arLabel;
        $arDados['values'] = $arValues;
        return $arDados;
    }

    public function getDadosSexo($codigoCidade){
        $dbSetePGAlunos = new \Db\SetePG\SeteAlunos();
        $arSexos = $dbSetePGAlunos->qtdAlunosSexo($codigoCidade);
        $arLabel = [];
        $arValues = [];
        foreach ($arSexos as $nivel){
            $arLabel[] = $nivel['nm_tp_sexo'];
            $arValues[] = $nivel['qtd'];

        } 
        $arDados['labels'] = $arLabel;
        $arDados['values'] = $arValues;
        return $arDados;
    }

    public function getDadosResponsavel($codigoCidade){
        $dbSetePGAlunos = new \Db\SetePG\SeteAlunos();
        $arResponsaveis = $dbSetePGAlunos->qtdAlunosResponsavel($codigoCidade);
        $arLabel = [];
        $arValues = [];
        foreach ($arResponsaveis as $nivel){
            $arLabel[] = $nivel['nm_tp_responsavel'];
            $arValues[] = $nivel['qtd'];

        } 
        $arDados['labels'] = $arLabel;
        $arDados['values'] = $arValues;
        return $arDados;
    }
    
}
