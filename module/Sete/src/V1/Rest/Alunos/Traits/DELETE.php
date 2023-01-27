<?php

namespace Sete\V1\Rest\Alunos\Traits;
use Laminas\ApiTools\ApiProblem\ApiProblem;

trait DELETE {
     /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id) {
        $this->usuarioPodeGravar();
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $this->processarRequestDELETE($codigoCidade, $idAluno);
    }

    private function processarRequestDELETE($codigoCidade, $idAluno) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            if (isset($arParams['rota'])) {
                $this->processarRotasDELETE($arParams);
            } else {
                $modelAlunos = new AlunosModel();
                $arResult = $modelAlunos->removerRegistroById($codigoCidade, $idAluno);
                $this->populaResposta(200, $arResult, false);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasDELETE($arParams) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $rota = $arParams['rota'];
        switch ($rota) {
            case 'escola':
                $this->removerEscolaAluno($codigoCidade, $idAluno);
                break;
            case 'rota':
                $this->removerRotaAluno($codigoCidade, $idAluno);
                break;
            default:
                $this->populaResposta(404, ['result' => false, 'messages' => "Recurso não encontrado!"], false);
                break;
        }
    }

    private function removerEscolaAluno($codigoCidade, $idAluno) {
        $dbSeteEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_aluno'] = $idAluno;
        $arEscolaAluno = $dbSeteEscolaTemAluno->getById($arIds);
        if(isset($arEscolaAluno['id_escola']) && !empty($arEscolaAluno['id_escola'])){
            $arIds['id_escola'] = $arEscolaAluno['id_escola'];
            $arResult = $dbSeteEscolaTemAluno->_delete($arIds);
            $codigoHTTP = 200;
        }else{
            $arResult = ['result' => false, 'messages' => 'Não há escola associada ao aluno.'];
            $codigoHTTP = 404;
        }       
        $this->populaResposta($codigoHTTP, $arResult, false);
        exit;
    }
    
    private function removerRotaAluno($codigoCidade, $idAluno) {
        $dbSeteRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_aluno'] = $idAluno;
        $arResult = $dbSeteRotaAtendeAluno->_delete($arIds);
        $this->populaResposta(200, $arResult, false);
        exit;
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data) {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }
}
