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
        $arParams = $this->event->getRouteMatch()->getParams();
        $userType = $this->event->getRouteMatch()->getParam('user_type');
        $codigoCidade = $this->event->getRouteMatch()->getParam('codigo_cidade');
        switch ($userType) {
            case 'api':
                $validate = $this->_model->validarUsuario($data);
                if (!$validate['result']) {
                    $this->populaResposta(400, $validate, false);
                } else {
                    $arResult = $this->_model->processarInsert($data, $this->getAcessToken());
                    $this->populaResposta(201, $arResult, false);
                }
                break;
            case 'sete':
                $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
                if ($usuarioPodeAcessarMunicipio) {
                    if (!isset($codigoCidade) || empty($codigoCidade)) {
                        $this->populaResposta(400, ['result' => false, 'messages' => "O código da cidade deve ser informado!"], false);
                    } else {
                        $dbMunicipio = new \Db\SetePG\GlbMunicipios();
                        if (!$dbMunicipio->municipioExiste($codigoCidade)) {
                            $this->populaResposta(400, ['result' => false, 'messages' => "O código da cidade não existe. Verifique e tente novamente!"], false);
                        }
                    }

                    if (isset($arParams['recurso'])) {
                        $this->processarRecursoUsuarios($arParams);
                    } else {
                        $boValidate = $this->_model->validarUsuarioSETE($data);
                        if (!$boValidate['result']) {
                            $this->populaResposta(400, $boValidate, false);
                        } else {
                            $data->codigo_cidade = $codigoCidade;
                            $arResult = $this->_model->processarInsertUsuarioSETE($data, $this->getAcessToken());
                            $this->populaResposta(201, $arResult, false);
                        }
                    }
                } else {
                    $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
                }
                break;
        }
    }

    private function processarRecursoUsuarios($arParams) {
        switch ($arParams['recurso']) {
            case 'foto':
                $this->uploadFotoUsuario($arParams);
                break;
            default:
                $this->populaResposta(404, ['result' => false, 'messages' => 'Recurso não encontrado.'], false);
                break;
        }
    }

    private function uploadFotoUsuario($arParams) {
        
        var_dump($arParams);
        
        var_dump($_POST);
        
        exit;
        $dbSeteUsuarios = new \Db\SetePG\SeteUsuarios();
        $idUsuario = $arParams['user_id'];
        $usuarioExiste = $dbSeteUsuarios->usuarioExisteById($idUsuario, $arParams['codigo_cidade']);
        if (!$usuarioExiste) {
            $this->populaResposta(404, ['result' => false, 'messages' => 'Usuário não encontrado.'], false);
        } else {
            if (!isset($_FILES['picture']) || !empty($_FILES['picture'])) {


                //$uploaddir = '/var/www/uploads/';
                //$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

                var_dump($_FILES);
                //if (move_uploaded_file($_FILES['picture']['tmp_name'], $uploadfile)) {

            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => 'Campo picture vazio.'], false);
            }
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
        $userType = $this->event->getRouteMatch()->getParam('user_type');
        $codigoCidade = $this->event->getRouteMatch()->getParam('codigo_cidade');
        switch ($userType) {
            case 'api':

                break;
            case 'sete':
                $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
                if ($usuarioPodeAcessarMunicipio) {
                    $this->populaResposta(200, $this->_model->getById($id, $codigoCidade), false);
                } else {
                    $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
                }

                break;
        }
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = []) {
        $userType = $this->event->getRouteMatch()->getParam('user_type');
        $codigoCidade = $this->event->getRouteMatch()->getParam('codigo_cidade');
        $userId = $this->event->getRouteMatch()->getParam('user_id');
        switch ($userType) {
            case 'api':
                $pagina = (isset($_GET['pagina']) ? $_GET['pagina'] : 1);
                $busca = (isset($_GET['busca']) ? $_GET['busca'] : "");
                $this->populaResposta(200, $this->_model->getListaPaginada($pagina, $busca), false);
                break;
            case 'sete':
                $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
                if ($usuarioPodeAcessarMunicipio) {
                    if (isset($userId)) {
                        $this->fetch($userId);
                    } else {
                        $this->populaResposta(200, $this->_model->getListaTodosUsuariosSETE($codigoCidade), true);
                    }
                } else {
                    $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
                }
                break;
            case 'logout':
                $this->logout();
                break;
        }
    }

    private function logout() {
        $accessToken = $this->getAcessToken();
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $dbCoreAccessToken->_delete($accessToken);
        $this->populaResposta(200, ['result' => true, 'messages' => 'Logout efetuado com sucesso!'], false);
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
        $params = $this->event->getRouteMatch()->getParams();
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
                $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($params['codigo_cidade']);
                if ($params['user_id'] === 'alterar-senha' && $usuarioPodeAcessarMunicipio) {
                    $this->processarAlterarSenhaUsuario($params['codigo_cidade'], $data);
                } else if (!$usuarioPodeAcessarMunicipio) {
                    $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
                }



                break;
        }
    }

    private function processarAlterarSenhaUsuario($codigoCidade, $arData) {
        $modelUsuario = new UserModel();
        $boValidate = $modelUsuario->validarTrocaSenhaUsuario($arData);
        if (!$boValidate['result']) {
            $this->populaResposta(400, $boValidate, false);
        } else {
            $dbCoreUsuario = new \Db\SetePG\SeteUsuarios();
            $arUsuario = $dbCoreUsuario->getById($arData->id_usuario, $codigoCidade);
            if ($arUsuario['password'] === $arData->senha_atual) {
                $arResult = $dbCoreUsuario->_atualizar([
                    'codigo_cidade' => $codigoCidade,
                    'id_usuario' => $arData->id_usuario
                        ], ['password' => $arData->nova_senha]);
                $this->populaResposta(200, $arResult, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => 'Senha não confere. Tente novamente!'], false);
            }
        }
    }

}
