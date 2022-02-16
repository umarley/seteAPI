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
        $arDados->cpf_monitor = $idMonitor;
        $arDados->codigo_cidade = $codigoCidade;
        switch ($rota) {
            case 'rota':
                $this->associarRotaMonitor($arDados);
                break;
        }
    }

    private function associarRotaMonitor($arDados) {
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $dbSeteRotaAtendidaPorMonitor = new \Db\SetePG\SeteRotaAtendidaPorMonitor();
        if ($arDados->id_rota !== "") {
            if (!$dbSeteRotas->rotaExiste($arDados->id_rota, $arDados->codigo_cidade)) {
                $this->populaResposta(404, ['result' => false, 'messages' => "Rota informada não existe!"], false);
            } else if ($dbSeteRotaAtendidaPorMonitor->monitorAssociadoRota($arDados->cpf_monitor, $arDados->codigo_cidade, $arDados->id_rota)) {
                $this->populaResposta(400, ['result' => false, 'messages' => "Monitor já associado a esta rota. Verifique e tente novamente!"], false);
            } else {
                $this->populaResposta(201, $dbSeteRotaAtendidaPorMonitor->_inserir([
                            'codigo_cidade' => $arDados->codigo_cidade,
                            'id_rota' => $arDados->id_rota,
                            'cpf_monitor' => $arDados->cpf_monitor
                        ]), false);
            }
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_rota deve ser informado!"], false);
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
        $data = $this->getBody();
        if (!isset($data->id_rota) || empty($data->id_rota)) {
            $this->populaResposta(400, ['result' => false, 'messages' => 'Informe o ID da rota que será removida para continuar!'], false);
        }
        $idRota = $data->id_rota;
        $rota = $arParams['rota'];
        switch ($rota) {
            case 'rota':
                $this->removerRotaMonitor($codigoCidade, $cpfMonitor, $idRota);
                break;
        }
    }

    private function removerRotaMonitor($codigoCidade, $cpfMonitor, $idRota) {

        $dbSeteRotaAtendidaPorMonitor = new \Db\SetePG\SeteRotaAtendidaPorMonitor();
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['cpf_monitor'] = $cpfMonitor;
        $arIds['id_rota'] = $idRota;
        $arResult = $dbSeteRotaAtendidaPorMonitor->_delete($arIds);
        $this->populaResposta(200, $arResult, false);
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
            if (isset($arParams['rota'])) {
                $rota = $arParams['rota'];
                $this->processarGetMonitorRota($rota, $codigoCidade, $cpfMonitor);
            } else if ($cpfMonitor != "" && is_numeric($cpfMonitor)) {
                $arMonitor = $modelMonitores->getById($codigoCidade, $cpfMonitor);
                $this->populaResposta(count($arMonitor) > 1 ? 200 : 404, $arMonitor, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro cpf_monitor deve ser informado!"], false);
            }
        }
    }

    private function processarGetMonitorRota($rota, $codigoCidade, $cpfMonitor) {
        if ($cpfMonitor != "") {
            switch ($rota) {
                case 'rota':
                    $this->getRotasMonitor($codigoCidade, $cpfMonitor);
                    break;
                default:
                    $arResult = ['result' => false, 'messages' => "Recurso não existe!"];
                    break;
            }
            $this->populaResposta(count($arResult) > 1 ? 200 : 404, $arResult, false);
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro cpf_monitor deve ser informado!"], false);
        }
    }

    private function getRotasMonitor($codigoCidade, $cpfMonitor) {
        $dbRotaAtendidaPorMonitor = new \Db\SetePG\SeteRotaAtendidaPorMonitor();
        $arIds['cpf_monitor'] = $cpfMonitor;
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta = $dbRotaAtendidaPorMonitor->getByCPFMonitor($arIds);
        $this->populaResposta(count($arResposta) > 0 ? 200 : 404, $arResposta, true);
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
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $this->processarRequestPUT($codigoCidade, $data);
    }

    private function processarRequestPUT($codigoCidade, $arData) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arData->codigo_cidade = $codigoCidade;
            $this->processarUpdateMonitor($arData);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarUpdateMonitor($data) {
        $modelMonitor = new MonitoresModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $cpfMonitor = $arParams['monitores_id'];
        $boValidate = $modelMonitor->validarUpdate($data, $cpfMonitor);
        if (empty($codigoCidade) || $cpfMonitor == "") {
            $this->populaResposta(400, ['result' => false, 'messages' => "O CPF do monitor e código da cidade devem ser informados!"], false);
        } else if ($boValidate['result']) {
            $this->populaResposta(200, $modelMonitor->prepareUpdate($codigoCidade, $cpfMonitor, $data), false);
        } else {
            $this->populaResposta(400, $boValidate, false);
        }
    }

}
