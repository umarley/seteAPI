<?php
namespace Sete\V1\Rest\Rotas;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class RotasResource extends API
{
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
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
                $this->processarInsertRota($arData);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasPOST($arParams, $arDados) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idRota = $arParams['rotas_id'];
        $rota = $arParams['rota'];
        $arDados->id_aluno = $idRota;
        $arDados->codigo_cidade = $codigoCidade;
        switch ($rota) {
            case 'veiculos':
                $this->associarVeiculoRota($arDados);
                break;
            default:
                $this->populaResposta(404, ['result' => false, 'messages' => "O recurso não existe!"], false);
                break;
        }
    }
    
    private function associarVeiculoRota($arDados) {
        $dbSeteVeiculos = new \Db\SetePG\SeteVeiculos();
        $dbSeteRotaPossuiVeiculos = new \Db\SetePG\SeteRotaPossuiVeiculo();
        if ($arDados->id_rota !== "") {
            if (!$dbSeteVeiculos->veiculoExiste($arDados->id_veiculo, $arDados->codigo_cidade)) {
                $this->populaResposta(404, ['result' => false, 'messages' => "Veículo informada não existe!"]);
            } else if ($dbSeteRotaPossuiVeiculos->rotaAssociadoVeiculo($arDados->id_veiculo, $arDados->codigo_cidade)) {
                $this->populaResposta(400, ['result' => false, 'messages' => "Aluno já associado a uma escola!"], false);
            } else {
                $this->populaResposta(201, $dbSeteRotaPossuiVeiculos->_inserir([
                            'codigo_cidade' => $arDados->codigo_cidade,
                            'id_rota' => $arDados->id_rota,
                            'id_veiculo' => $arDados->id_veiculo
                        ]), false);
            }
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_rota deve ser informado!"], false);
        }
    }
    
    private function processarInsertRota($arData) {
        $modelRotas = new RotasModel();
        $boValidate = $modelRotas->validarInsert($arData);
        if ($boValidate['result']) {
            $arResult = $modelRotas->prepareInsert($arData);
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
    public function delete($id)
    {
        $this->usuarioPodeGravar();
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idRota = $arParams['rotas_id'];
        $this->processarRequestDELETE($codigoCidade, $idRota);
    }
    
    private function processarRequestDELETE($codigoCidade, $idRota) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            if (isset($arParams['rota'])) {
                $this->processarRotasDELETE($arParams);
            } else {
                $modelRotas = new RotasModel();
                $arResult = $modelRotas->removerRegistroById($codigoCidade, $idRota);
                $this->populaResposta(200, $arResult, false);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasDELETE($arParams) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idRota = $arParams['rotas_id'];
        $rota = $arParams['rota'];
        switch ($rota) {
            case 'veiculos':
                $this->removerVeiculoRota($codigoCidade, $idRota);
                break;
        }
    }
    
    private function removerVeiculoRota($codigoCidade, $idRota) {
        $dbSeteRotaPossuiVeiculo = new \Db\SetePG\SeteRotaPossuiVeiculo();
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_rota'] = $idRota;
        $arResult = $dbSeteRotaPossuiVeiculo->_delete($arIds);
        $this->populaResposta(200, $arResult, false);
        exit;
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        $modelRotas = new RotasModel();
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
            $idRota = $arParams['rotas_id'];
            $rota = $arParams['rota'];
            if (isset($rota)) {
                $this->processarGetRota($rota, $codigoCidade, $idRota);
            } else if ($idRota != "" && is_numeric($idRota)) {
                $arRota = $modelRotas->getById($codigoCidade, $idRota);
                $this->populaResposta(count($arRota) > 1 ? 200 : 404, $arRota, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_rota deve ser informado!"], false);
            }
        }
    }
    
    private function processarGetRota($rota, $codigoCidade, $idRota) {
        if ($idRota != "" && is_numeric($idRota)) {
            switch ($rota) {
                case 'veiculos':
                    $this->getVeiculosRota($codigoCidade, $idRota);
                    break;
                default:
                    $arResult = ['result' => false, 'messages' => "Recurso não existe!"];
                    break;
            }
            $this->populaResposta(count($arResult) > 1 ? 200 : 404, $arResult, false);
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_aluno deve ser informado!"], false);
        }
    }
    
    private function getVeiculosRota($codigoCidade, $idRota){
        $dbRotaPossuiVeiculo = new \Db\SetePG\SeteRotaPossuiVeiculo();
        $arIds['id_rota'] = $idRota;
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta = $dbRotaPossuiVeiculo->getById($arIds);
        $this->populaResposta(count($arResposta) > 1 ? 200 : 404, $arResposta, false);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
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
            $this->obterTodasRotasCidade($codigoCidade);
        }
    }
    
    private function obterTodasRotasCidade($codigoCidade) {
        $modelRotas = new RotasModel();
        $arRotas = $modelRotas->getAll($codigoCidade);
        $arResultado['data'] = $arRotas;
        $arResultado['total'] = count($arRotas);
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept');
        header("Content-type: application/json");
        echo json_encode($arResultado);
        exit;
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        $this->usuarioPodeGravar();
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
