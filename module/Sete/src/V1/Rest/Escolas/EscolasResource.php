<?php

namespace Sete\V1\Rest\Escolas;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class EscolasResource extends API {

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
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
                $this->processarInsertEscola($arData);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasPOST($arParams, $arDados) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idEscola = $arParams['escolas_id'];
        $rota = $arParams['rota'];
        $arDados->id_escola = $idEscola;
        $arDados->codigo_cidade = $codigoCidade;
        switch ($rota) {
            case 'alunos':
                if (isset($arDados->alunos)) {
                    $this->processarAssociacaoAlunos($arDados);
                } else {
                    $this->populaResposta(400, ['result' => false, 'messages' => "Deve ser enviado o array alunos com os ids dos alunos que se desejam associar a escola."], false);
                }
                break;
            case 'rotas':
                if (isset($arDados->rotas)) {
                    $this->processarAssociacaoRotas($arDados);
                } else {
                    $this->populaResposta(400, ['result' => false, 'messages' => "Deve ser enviado o array rotas com os ids das rotas que se desejam associar a escola."], false);
                }
                break;
            default:
                $this->populaResposta(404, ['result' => false, 'messages' => "Recurso não encontrado."], false);
                break;
        }
    }

    private function processarInsertEscola($data) {
        $modelEscolas = new EscolasModel();
        $boValidate = $modelEscolas->validarInsert($data);
        if ($boValidate['result']) {
            $arResult = $modelEscolas->prepareInsert($data);
            $this->populaResposta($arResult['result'] ? 201 : 400, $arResult, false);
        } else {
            $this->populaResposta(400, $boValidate, false);
        }
    }

    /**
     * Método responsável por associar vários alunos de uma única vez a escola
     * @param Object $arDados
     */
    private function processarAssociacaoAlunos($arDados) {
        $modelEscolas = new EscolasModel();
        $arResult = $modelEscolas->associarVariosAlunos($arDados->codigo_cidade, $arDados->id_escola, $arDados->alunos);
        $this->populaResposta(200, $arResult);
    }
    
    /**
     * Método responsável por processar a associaçãod e várias rotas a uma escola
     * @param type $arDados
     */
    
    private function processarAssociacaoRotas($arDados){
        $modelEscolas = new EscolasModel();
        $arResult = $modelEscolas->associarVariasRotas($arDados->codigo_cidade, $arDados->id_escola, $arDados->rotas);
        $this->populaResposta(200, $arResult);
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id) {
        $this->usuarioPodeGravar();
        $modelEscolas = new EscolasModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        if (isset($arParams['rota'])) {
            $client_data = file_get_contents("php://input");
            $arData = json_decode($client_data);
            $this->processarRotasDELETE($arParams, $arData);
        } else {
            $codigoCidade = $arParams['codigo_cidade'];
            $idEscola = $arParams['escolas_id'];
            $arResult = $modelEscolas->removerRegistroById($codigoCidade, $idEscola);
            $this->populaResposta(200, $arResult);
        }
        exit;
    }

    private function processarRotasDELETE($arParams, $arDados) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idEscola = $arParams['escolas_id'];
        $rota = $arParams['rota'];
        $arDados->id_escola = $idEscola;
        $arDados->codigo_cidade = $codigoCidade;
        switch ($rota) {
            case 'alunos':
                if (isset($arDados->alunos)) {
                    $this->processarDelecaoAssociacaoAlunos($arDados);
                } else {
                    $this->populaResposta(400, ['result' => false, 'messages' => "Deve ser enviado o array alunos com os ids dos alunos que se desejam remover a associação com a escola."], false);
                }
                break;
            case 'rotas':
                if (isset($arDados->rotas)) {
                    $this->processarDelecaoAssociacaoRotas($arDados);
                } else {
                    $this->populaResposta(400, ['result' => false, 'messages' => "Deve ser enviado o array rotas com os ids das rotas que se desejam remover a associação com a escola."], false);
                }
                break;
            default:
                $this->populaResposta(404, ['result' => false, 'messages' => "Recurso não encontrado."], false);
                break;
        }
    }

    private function processarDelecaoAssociacaoAlunos($arDados) {
        $modelEscolas = new EscolasModel();
        $arResult = $modelEscolas->excluirVariasAssociacoesAlunos($arDados->codigo_cidade, $arDados->id_escola, $arDados->alunos);
        $this->populaResposta(200, $arResult);
    }
    
    private function processarDelecaoAssociacaoRotas($arDados){
        $modelEscolas = new EscolasModel();
        $arResult = $modelEscolas->excluirVariasAssociacoesRotas($arDados->codigo_cidade, $arDados->id_escola, $arDados->rotas);
        $this->populaResposta(200, $arResult);
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

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id) {
        $modelEscola = new EscolasModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $dbGlbMunicipios = new \Db\SetePG\GlbMunicipios();
        $codigoCidade = $arParams['codigo_cidade'];
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro codigo_cidade deve ser informado!"], false);
        } else if (!$dbGlbMunicipios->municipioExiste($codigoCidade)) {
            $this->populaResposta(404, ['result' => false, 'messages' => "O municipio informado não existe!"], false);
        } else if (!$this->usuarioPodeAcessarCidade($codigoCidade)) {
            $this->populaResposta(403, ['result' => false, 'messages' => "Usuário sem permissão para acessar o municipio informado!"], false);
        } else {
            $idEscola = $arParams['escolas_id'];
            $rota = $arParams['rota'];
            if (isset($rota)) {
                $this->processarGetEscolaRota($rota, $codigoCidade, $idEscola);
            } else if ($idEscola != "" && is_numeric($idEscola)) {
                $arEscola = $modelEscola->getById($codigoCidade, $idEscola);
                $this->populaResposta(count($arEscola) > 1 ? 200 : 404, $arEscola, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_aluno deve ser informado!"], false);
            }
        }
    }

    private function processarGetEscolaRota($rota, $codigoCidade, $idEscola) {
        if ($idEscola != "" && is_numeric($idEscola)) {
            switch ($rota) {
                case 'alunos':
                    $this->getAlunosEscola($codigoCidade, $idEscola);
                    break;
                case 'rotas':
                    $this->getRotasEscola($codigoCidade, $idEscola);
                    break;
                default:
                    $arResult = ['result' => false, 'messages' => "Recurso não existe!"];
                    break;
            }
            $this->populaResposta(count($arResult) > 1 ? 200 : 404, $arResult, false);
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_escola deve ser informado!"], false);
        }
    }

    private function getAlunosEscola($codigoCidade, $idEscola) {
        $dbSeteEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        $arResult = $dbSeteEscolaTemAluno->getAlunosByEscola($codigoCidade, $idEscola);
        $this->populaResposta(count($arResult) > 1 ? 200 : 404, $arResult);
    }
    
    private function getRotasEscola($codigoCidade, $idEscola){
        $dbSeteRotaPassaPorEscola = new \Db\SetePG\SeteRotaPassaPorEscola();
        $arResult = $dbSeteRotaPassaPorEscola->getRotasByEscola($codigoCidade, $idEscola);
        $this->populaResposta(count($arResult) > 1 ? 200 : 404, $arResult);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = []) {
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $dbGlbMunicipios = new \Db\SetePG\GlbMunicipios();
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro codigo_cidade deve ser informado!"], false);
        } else if (!$dbGlbMunicipios->municipioExiste($codigoCidade)) {
            $this->populaResposta(404, ['result' => false, 'messages' => "O municipio informado não existe!"], false);
        } else if (!$this->usuarioPodeAcessarCidade($codigoCidade)) {
            $this->populaResposta(403, ['result' => false, 'messages' => "Usuário sem permissão para acessar o municipio informado!"], false);
        } else {
            $this->obterTodasEscolasCidade($codigoCidade);
        }
    }

    private function obterTodasEscolasCidade($codigoCidade) {
        $modelEscolas = new EscolasModel();
        $arEscolas = $modelEscolas->getAll($codigoCidade);
        $this->populaResposta(200, $arEscolas);
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data) {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data) {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data) {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data) {
        $this->usuarioPodeGravar();
        $modelEscolas = new EscolasModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idEscola = $arParams['escolas_id'];
        $boValidate = $modelEscolas->validarUpdate($data);
        if (empty($codigoCidade) || $idEscola == "") {
            return ['result' => false, 'messages' => "O ID escola e código da cidade devem ser informados!"];
        } else if ($boValidate['result']) {
            return $modelEscolas->prepareUpdate($codigoCidade, $idEscola, $data);
        } else {
            return $boValidate;
        }
    }

}
