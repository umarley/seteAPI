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
        $arDados->id_aluno = $idAluno;
        $arDados->codigo_cidade = $codigoCidade;
        switch ($rota) {
            case 'aluno':
                
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
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id) {
        $modelEscolas = new EscolasModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idEscola = $arParams['escolas_id'];
        $arResult = $modelEscolas->removerRegistroById($codigoCidade, $idEscola);
        header("Content-type: application/json");
        echo json_encode($arResult);
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
    
    private function processarGetEscolaRota($rota, $codigoCidade, $idEscola){
        if ($idEscola != "" && is_numeric($idEscola)) {
            switch ($rota) {
                case 'alunos':
                    $this->getAlunosEscola($codigoCidade, $idEscola);
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
    
    private function getAlunosEscola($codigoCidade, $idEscola){
        $dbSeteEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        $arResult = $dbSeteEscolaTemAluno->getAlunosByEscola($codigoCidade, $idEscola);
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
