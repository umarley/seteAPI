<?php

namespace Sete\V1\Rest\Fornecedores;

use Sete\V1\Rest\Fornecedores;
use Sete\V1\API;
use Laminas\ApiTools\ApiProblem\ApiProblem;


class FornecedoresResource extends API {

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
            $arData->codigo_cidade = $codigoCidade;
            $this->processarInsertFornecedor($arData);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarInsertFornecedor($arData) {
        $modelFornecedores = new FornecedoresModel();
        $boValidate = $modelFornecedores->validarInsert($arData);
        if ($boValidate['result']) {
            $arResult = $modelFornecedores->prepareInsert($arData);
            $this->populaResposta(200, $arResult, false);
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
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idVeiculo = $arParams['fornecedores_id'];
        $this->processarRequestDELETE($codigoCidade, $idVeiculo);
    }

    private function processarRequestDELETE($codigoCidade, $idVeiculo) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            $modelFornecedores = new FornecedoresModel();
            $arResult = $modelFornecedores->removerRegistroById($codigoCidade, $idVeiculo);
            $this->populaResposta(200, $arResult, false);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
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
        $modelFornecedores = new FornecedoresModel();
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
            $idFornecedor = $arParams['fornecedores_id'];
            if ($idFornecedor != "" && is_numeric($idFornecedor)) {
                $arFornecedor = $modelFornecedores->getById($codigoCidade, $idFornecedor);
                $this->populaResposta(count($arFornecedor) > 1 ? 200 : 404, $arFornecedor, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_Fornecedor deve ser informado!"], false);
            }
        }
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
            $this->obterTodosFornecedoresCidade($codigoCidade);
        }
    }

    private function obterTodosFornecedoresCidade($codigoCidade) {
        $modelFornecedores = new FornecedoresModel();
        $arFornecedores = $modelFornecedores->getAll($codigoCidade);
        $arResultado['data'] = $arFornecedores;
        $arResultado['total'] = count($arFornecedores);
        $this->populaResposta(200, $arResultado, false);
        exit;
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
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $this->processarRequestPUT($codigoCidade, $data);
    }

    private function processarRequestPUT($codigoCidade, $arData) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arData->codigo_cidade = $codigoCidade;
            $this->processarUpdateFornecedor($arData);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarUpdateFornecedor($data) {
        $modelFornecedores = new FornecedoresModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idFornecedor = $arParams['fornecedores_id'];
        $boValidate = $modelFornecedores->validarUpdate($data, $idFornecedor);
        if (empty($codigoCidade) || $idFornecedor == "") {
            $this->populaResposta(400, ['result' => false, 'messages' => "O ID fornecedor e código da cidade devem ser informados!"], false);
        } else if ($boValidate['result']) {
            $this->populaResposta(200, $modelFornecedores->prepareUpdate($codigoCidade, $idFornecedor, $data), false);
        } else {
            $this->populaResposta(400, $boValidate, false);
        }
    }

}
