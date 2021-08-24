<?php

namespace Sete\V1\Rest\Authenticator;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class AuthenticatorResource extends AbstractResourceListener {

    public function __construct() {
        $this->_model = new AuthenticatorModel();
    }

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data) {
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $conteudo = file_get_contents("php://input");
        $arPost = json_decode($conteudo, true);
        if (isset($arParams['tipo']) && $arParams['tipo'] === 'sete') {
            $arResult = $this->_model->autenticarUsuarioSETE($arPost);
        } else {
            $arResult = $this->_model->autenticarUsuario($arPost);
        }

        return $arResult;
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
        $headers = apache_request_headers();
        $arParams = $this->event->getRouteMatch()->getParams();
        if (key_exists('Authorization', $headers)) {
            $accessToken = $headers['Authorization'];
            if (!empty($accessToken)) {
                $valido = $this->_model->validarAccessToken($accessToken);
                if ($valido) {
                    if($arParams['tipo'] === 'sete'){
                        $dbSeteUsuario = new \Db\SetePG\SeteUsuarios();
                        return ['result' => true, 'data' => $dbSeteUsuario->getUsuarioByAccessToken($accessToken)];
                    }else{
                        return ['result' => true, 'messages' => 'Access Token válido!'];
                    }
                } else {
                    return ['result' => false, 'messages' => 'Access Token inválido!'];
                }
            } else {
                return new ApiProblem(406, 'Cabeçalho Authorization vazio.');
            }
        } else {
            return new ApiProblem(406, 'Cabeçalho Authorization ausente.');
        }
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
        echo "Umarley";
        exit;
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
       $arParams = $this->getEvent()->getRouteMatch()->getParams();
       var_dump($arParams);
       exit;
        $conteudo = file_get_contents("php://input");
        $arPost = json_decode($conteudo, true);
        if (isset($arParams['tipo']) && $arParams['tipo'] === 'sete') {
            $arResult = $this->_model->autenticarUsuarioSETE($arPost);
        } else {
            $arResult = $this->_model->autenticarUsuario($arPost);
        }
    }

}
