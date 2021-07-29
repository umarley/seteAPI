<?php

namespace Sete\V1\Rest\Registro;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class RegistroResource extends AbstractResourceListener {

    private $_model;
    
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data) {
        $this->_model = new \Sete\V1\Rest\User\UserModel();
        $codigoCidade = $this->event->getRouteMatch()->getParam('registro_id');
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            $this->populaResposta(400, ['result' => false, 'messages' => "O código da cidade deve ser informado!"], false);
        } else {
            $dbMunicipio = new \Db\SetePG\GlbMunicipios();
            if (!$dbMunicipio->municipioExiste($codigoCidade)) {
                $this->populaResposta(400, ['result' => false, 'messages' => "O código da cidade não existe. Verifique e tente novamente!"], false);
            }
        }
        $boValidate = $this->_model->validarUsuarioSETE($data);
        if (!$boValidate['result']) {
            $this->populaResposta(400, $boValidate, false);
        } else {
            $data->codigo_cidade = $codigoCidade;
            $arResult = $this->_model->processarInsertUsuarioSETE($data, $this->getAcessToken());
            $this->populaResposta(201, $arResult, false);
        }
    }
    
    private function populaResposta($codigoStatus, $arResposta, $retornaLista = true) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept');
        header('Content-Type: application/json', true, $codigoStatus);

        if ($retornaLista) {
            $arResult['data'] = $arResposta;
            $arResult['total'] = count($arResposta);
        }else{
            $arResult = $arResposta;
        }
        
        if($codigoStatus !== 404){
            $arResult['result'] = isset($arResposta['result']) ? $arResposta['result'] : true;
        }else{
            $arResult['result'] = false;
        }

        echo json_encode($arResult);
        exit;
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id) {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
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
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = []) {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
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
