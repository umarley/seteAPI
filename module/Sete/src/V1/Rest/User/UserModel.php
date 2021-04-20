<?php

namespace Sete\V1\Rest\User;

class UserModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\Core\Usuario();
    }

    public function getAll() {
        $arDados = $this->_entity->getLista();
        return $arDados;
    }

    public function getById($codigo) {
        $dbSeteEscolas = new \Db\Sete\SeteEscolas();
        $dbSeteAlunos = new \Db\Sete\SeteAlunos();
        $dbSeteVeiculos = new \Db\Sete\SeteVeiculos();
        $dbSeteRotas = new \Db\Sete\SeteRotas();
        $arData = $this->_entity->getByCodigoIBGE($codigo);
        return $arData;
    }

    public function getListaPaginada($pagina, $busca = "") {
        $qtdPerPage = 20;
        $totalRegistros = $this->_entity->getTotalUsuarios($busca);
        $qtdPaginas = ceil($totalRegistros / $qtdPerPage);
        $offset = ($qtdPerPage * $pagina) - $qtdPerPage;
        $arData = $this->_entity->getLista($offset, $qtdPerPage, $busca);
        return [
            'qtd_registros' => (int) $totalRegistros,
            'pages' => (int) $qtdPaginas,
            'reg_por_pagina' => (int) $qtdPerPage,
            'pg_atual' => (int) $pagina,
            'registros' => $arData
        ];
    }

    public function processarInsert($arPost, $accessToken) {
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $arData = (array) $arPost;
        $arData['dt_criacao'] = date("Y-m-d H:i:s");
        $arData['criado_por'] = $dbCoreAccessToken->getEmailUsuarioByAccessToken($accessToken);
        return $this->_entity->_inserir($arData);
    }

    public function processarUpdate($idUsuario, $arPost, $accessToken) {
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $arData = (array) $arPost;
        $arData['dt_alteracao'] = date("Y-m-d H:i:s");
        $arData['alterado_por'] = $dbCoreAccessToken->getEmailUsuarioByAccessToken($accessToken);
        return $this->_entity->_atualizar($idUsuario, $arData);
    }

    public function validarUsuario($arPost) {
        $boValidate = true;
        $arErros = [];
        if (empty($arPost->nome)) {
            $boValidate = false;
            $arErros[] = "O parâmetro nome é obrigatório!";
        }
        if (empty($arPost->email) || !\Application\Utils\Utils::validarEmail($arPost->email)) {
            $boValidate = false;
            $arErros[] = "O parâmetro email deve ser válido!";
        }
        if (empty($arPost->senha) || !$this->isValidMd5($arPost->senha)) {
            $boValidate = false;
            $arErros[] = "O parâmetro senha deve ser um hash md5!";
        }

        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function validarUsuarioUpdate($arPost) {
        $boValidate = true;
        $arErros = [];
        $arValoresValidosIsAtivo = ['S', 'N'];
        if (isset($arPost->nome) && empty($arPost->nome)) {
            $boValidate = false;
            $arErros[] = "O parâmetro nome é obrigatório!";
        }
        if (isset($arPost->email)) {
            $boValidate = false;
            $arErros[] = "Não é possivel alterar o email do usuário!";
        }
        if (isset($arPost->senha) && (empty($arPost->senha) || !$this->isValidMd5($arPost->senha))) {
            $boValidate = false;
            $arErros[] = "O parâmetro senha deve ser um hash md5!";
        }
        if (isset($arPost->is_ativo) && !in_array($arPost->is_ativo, $arValoresValidosIsAtivo)) {
            $boValidate = false;
            $arErros[] = "O parâmetro is_ativo deve conter S ou N!";
        }
        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function isValidMd5($md5 = '') {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }
    
    public function getListaUsuariosSeteByCidade($codigoCidade, $busca){
        $dbSeteUsuario = new \Db\Sete\SeteUsuarios();
        return $dbSeteUsuario->getUsuariosLiberadosSistemaByCidade($codigoCidade, $busca);
    }

}
