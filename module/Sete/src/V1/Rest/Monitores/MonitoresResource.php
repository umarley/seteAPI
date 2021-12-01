<?php

namespace Sete\V1\Rest\Monitores;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class MonitoresResource extends API {

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
                $this->processarInsertMonitor($arData);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasPOST($arParams, $arDados) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idMonitor = $arParams['monitores_id'];
        $rota = $arParams['rota'];
        $arDados->cpf = $idMonitor;
        $arDados->codigo_cidade = $codigoCidade;
        switch ($rota) {
            case 'rota':
                $this->associarRotaMotorista($arDados);
                break;
        }
    }

    private function processarInsertMonitor($arData) {
        $modelMonitores = new MonitoresModel();
        $boValidate = $modelMonitores->validarInsert($arData);
        if ($boValidate['result']) {
            $arResult = $modelMonitores->prepareInsert($arData, $this->getAcessToken());
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
        $cpfMonitor = $arParams['monitores_id'];
        $this->processarRequestDELETE($codigoCidade, $cpfMonitor);
    }

    private function processarRequestDELETE($codigoCidade, $cpfMonitor) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            if (isset($arParams['rota'])) {
                $this->processarRotasDELETE($arParams);
            } else {
                $modelMonitores = new MonitoresModel();
                $arResult = $modelMonitores->removerRegistroById($codigoCidade, $cpfMonitor);
                $this->populaResposta(200, $arResult, false);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasDELETE($arParams) {
        $codigoCidade = $arParams['codigo_cidade'];
        $cpfMonitor = $arParams['monitores_id'];
        $rota = $arParams['rota'];
        switch ($rota) {
            case 'rota':
                $this->removerRotaMonitor($codigoCidade, $cpfMonitor);
                break;
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
        $modelMonitores = new MonitoresModel();
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
            $cpfMonitor = $arParams['monitores_id'];
            $rota = $arParams['rota'];
            if (isset($rota)) {
                $this->processarGetMotoristaRota($rota, $codigoCidade, $cpfMonitor);
            } else if ($cpfMonitor != "" && is_numeric($cpfMonitor)) {
                $arMonitor = $modelMonitores->getById($codigoCidade, $cpfMonitor);
                $this->populaResposta(count($arMonitor) > 1 ? 200 : 404, $arMonitor, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro cpf_monitor deve ser informado!"], false);
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
            $this->obterTodosMonitoresCidade($codigoCidade);
        }
    }

    private function obterTodosMonitoresCidade($codigoCidade) {
        $modelMonitores = new MonitoresModel();
        $arMonitores = $modelMonitores->getAll($codigoCidade);
        $arResultado['data'] = $arMonitores;
        $arResultado['total'] = count($arMonitores);
        $this->populaResposta(200, $arResultado, false);
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
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }

}
