<?php

namespace Sete\V1\Rest\User;

use Sete\V1\API;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class UserResource extends API {

    public function __construct() {
        parent::__construct();
        $this->_model = new UserModel();
    }

    public function create($data) {
        $userType = $this->event->getRouteMatch()->getParam('user_type');
        switch ($userType) {
            case 'api':
                $validate = $this->_model->validarUsuario($data);
                if (!$validate['result']) {
                    $this->populaResposta(400, $validate, false);
                } else {
                    $arResult = $this->_model->processarInsert($data, $this->getAcessToken());
                    $this->populaResposta(200, $arResult, false);
                }
                break;
            case 'sete':

                break;
        }
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

        $rota = $this->event->getRouteMatch();
        $paramsUri = $rota->getParams();
        $paramsQuery = $this->event->getQueryParams();

        var_dump($id);
        var_dump($paramsUri);
        var_dump($paramsQuery);

        echo "Email: " . $paramsQuery['email'];
        exit;
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = []) {
        $userType = $this->event->getRouteMatch()->getParam('user_type');
        switch ($userType) {
            case 'api':
                $pagina = (isset($_GET['pagina']) ? $_GET['pagina'] : 1);
                $busca = (isset($_GET['busca']) ? $_GET['busca'] : "");
                $this->populaResposta(200, $this->_model->getListaPaginada($pagina, $busca), false);
                break;
            case 'sete':

                break;
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
        $params = $this->event->getParams();
        $userType = $this->event->getRouteMatch()->getParam('user_type');
        switch ($userType) {
            case 'api':
                $validate = $this->_model->validarUsuarioUpdate($data);
                if (!$validate['result']) {
                    $this->populaResposta(400, $validate, false);
                } else {
                    $arResult = $this->_model->processarUpdate($id, $data, $this->getAcessToken());
                    $this->populaResposta(200, $arResult, false);
                }
                break;
            case 'sete':

                break;
        }
    }

}
