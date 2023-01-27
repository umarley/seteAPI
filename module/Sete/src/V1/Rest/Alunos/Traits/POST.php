<?php

namespace Sete\V1\Rest\Alunos\Traits;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Application\Utils\HttpCode;
use Sete\V1\Rest\Alunos\Models\AlunosModel;

trait POST {

    public function create($data) {
        $this->usuarioPodeGravar();
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $this->processarRequestPOST($codigoCidade, $data);
    }

    private function processarRequestPOST($codigoCidade, $arData) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            if (isset($arParams['rota'])) {
                $this->processarRotasPOST($arParams, $arData);
            } else {
                $arData->codigo_cidade = $codigoCidade;
                $this->processarInsertAluno($arData);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasPOST($arParams, $arDados) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $rota = $arParams['rota'];
        $arDados->id_aluno = $idAluno;
        $arDados->codigo_cidade = $codigoCidade;
        switch ($rota) {
            case 'escola':
                $this->associarEscolaAluno($arDados);
                break;
            case 'rota':
                $this->associarRotaAluno($arDados);
                break;
            default:
                $this->populaResposta(404, ['result' => false, 'messages' => 'Recurso não encontrado!'], false);
                break;
        }
    }

    private function associarEscolaAluno($arDados) {
        $dbSeteEscolas = new \Db\SetePG\SeteEscolas();
        $dbSeteEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        if ($arDados->id_escola !== "") {
            if (!$dbSeteEscolas->escolaExiste($arDados->id_escola, $arDados->codigo_cidade)) {
                $this->populaResposta(404, ['result' => false, 'messages' => "Escola informada não existe!"]);
            } else if ($dbSeteEscolaTemAluno->alunoAssociadoEscola($arDados->id_aluno, $arDados->codigo_cidade)) {
                $this->populaResposta(400, ['result' => false, 'messages' => "Aluno já associado a uma escola. Não é permitido o aluno ter mais de uma escola!"], false);
            } else {
                $this->populaResposta(201, $dbSeteEscolaTemAluno->_inserir([
                            'codigo_cidade' => $arDados->codigo_cidade,
                            'id_escola' => $arDados->id_escola,
                            'id_aluno' => $arDados->id_aluno
                        ]), false);
            }
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_escola deve ser informado!"], false);
        }
    }

    private function processarInsertAluno($arData) {
        $modelAlunos = new AlunosModel();
        $boValidate = $modelAlunos->validarInsert($arData);
        if ($boValidate['result']) {
            $arResult = $modelAlunos->prepareInsert($arData);
            $this->populaResposta(200, $arResult, false);
        } else {
            $this->populaResposta(400, $boValidate, false);
        }
    }
    
    private function associarRotaAluno($arDados) {
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $dbSeteRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        if ($arDados->id_rota !== "") {
            if (!$dbSeteRotas->rotaExiste($arDados->id_rota, $arDados->codigo_cidade)) {
                $this->populaResposta(404, ['result' => false, 'messages' => "Rota informada não existe!"], false);
             }else {
                $this->populaResposta(201, $dbSeteRotaAtendeAluno->_inserir([
                            'codigo_cidade' => $arDados->codigo_cidade,
                            'id_rota' => $arDados->id_rota,
                            'id_aluno' => $arDados->id_aluno
                        ]), false);
            }
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_rota deve ser informado!"], false);
        }
    }


}
