<?php

namespace Sete\V1\Rest\User;

use Symfony\Component\VarDumper\VarDumper;

class UserModel {

    protected $_entity;
    protected $_entityPG;

    public function __construct() {
        $this->_entity = new \Db\Core\Usuario();
        $this->_entityPG = new \Db\SetePG\SeteUsuarios();
    }

    public function getAll() {
        $arDados = $this->_entity->getLista();
        return $arDados;
    }

    public function getById($idUsuario, $codigoCidade) {
        
        $arData = $this->_entityPG->getById($idUsuario, $codigoCidade);
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
    
    public function getListaTodosUsuariosSETE($codigoMunicipio){
        return $this->_entityPG->getLista($codigoMunicipio);
    }

    public function processarInsertNovoUsuario($arPost) {
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $dbGlbMunicipios = new \Db\SetePG\GlbMunicipios();
        $arCidade = $dbGlbMunicipios->getByCodigo($arPost->codigo_cidade);
        $arData = (array) $arPost;
        $arData['is_liberado'] = 'N';
        //$arData['cod_cidade'] = $arData['codigo_cidade'];
        $arData['cidade'] = $arCidade['nm_cidade'];
        $arData['cod_estado'] = $arCidade['codigo_uf'];
        $arData['estado'] = $arCidade['estado'];
        $arData['dt_criacao'] = date("Y-m-d H:i:s");
        $arData['nivel_permissao'] = $arData['tipo_permissao'];
        unset($arData['tipo_permissao']);
        $arResult = $this->_entityPG->_inserir($arData);
        if ($arResult['result']) {
            $arResult['messages']['id'] = $this->_entityPG->getUltimoIdInserido();
        }
        return $arResult;
    }

    public function processarUpdate($idUsuario, $arPost, $accessToken) {
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $arData = (array) $arPost;
        $arData['nivel_permissao'] = $arData['tipo_permissao'];
        unset($arData['tipo_permissao']);
        $arData['dt_alteracao'] = date("Y-m-d H:i:s");
        $arData['alterado_por'] = $dbCoreAccessToken->getEmailUsuarioSETEByAccessToken($accessToken);
        $arId['codigo_cidade'] = $arPost->codigo_cidade;
        $arId['id_usuario'] = $idUsuario;
        return $this->_entityPG->_atualizar($arId, $arData);
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
    
    public function validarUsuarioSETE($arPost, $idUser, $codigoCidade) {
        $boValidate = true;
        $arErros = [];
        $listaTipoPermissao = ['admin', 'leitor', 'editor'];
        if(!$this->_entityPG->usuarioExisteById($idUser, $codigoCidade)){
            $boValidate = false;
            $arErros[] = "Não existe usuário com este id!";
        }
        if (empty($arPost->nome)) {
            $boValidate = false;
            $arErros[] = "O parâmetro nome é obrigatório!";
        }
        if (empty($arPost->cpf) || !\Application\Utils\Utils::validarCpf($arPost->cpf)) {
            $boValidate = false;
            $arErros[] = "Informe um CPF válido!";
        }else if($this->_entityPG->usuarioExiste($arPost->cpf)){
           $boValidate = false;
            $arErros[] = "O CPF informado já existe. Verifique e tente novamente!";     
        }
        if (empty($arPost->email) || !\Application\Utils\Utils::validarEmail($arPost->email)) {
            $boValidate = false;
            $arErros[] = "O parâmetro email deve ser válido!";
        }
        if (empty($arPost->password) || !$this->isValidMd5($arPost->password)) {
            $boValidate = false;
            $arErros[] = "O parâmetro password deve ser um hash md5!";
        }
        if (empty($arPost->tipo_permissao) || !in_array($arPost->tipo_permissao, $listaTipoPermissao)) {
            $boValidate = false;
            $arErros[] = "O parâmetro tipo_permissao é obrigatório!";
        }

        return ['result' => $boValidate, 'messages' => $arErros];
    }
    
    public function processarInsert($arPost, $accessToken){
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $dbGlbMunicipios = new \Db\SetePG\GlbMunicipios();
        $arCidade = $dbGlbMunicipios->getByCodigo($arPost->codigo_cidade);
        $arData = (array) $arPost;
        $arData['is_liberado'] = 'S';
        //$arData['cod_cidade'] = $arData['codigo_cidade'];
        $arData['cidade'] = $arCidade['nm_cidade'];
        $arData['cod_estado'] = $arCidade['codigo_uf'];
        $arData['estado'] = $arCidade['estado'];
        $arData['dt_criacao'] = date("Y-m-d H:i:s");
        $arData['criado_por'] = $dbCoreAccessToken->getEmailUsuarioSETEByAccessToken($accessToken);
        $arData['nivel_permissao'] = $arData['tipo_permissao'];
        unset($arData['tipo_permissao']);
        $arResult = $this->_entityPG->_inserir($arData);
        if ($arResult['result']) {
            $arResult['messages']['id'] = $this->_entityPG->getUltimoIdInserido();
        }
        return $arResult;
    }

    public function removerRegistroById($codigoCidade, $idUser) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_usuario'] = $idUser;
        $arResult = $this->_entityPG->_delete($arIds);
        return $arResult;
    }
    
    public function getListaUsuariosSeteByCidade($codigoCidade, $busca){
        $dbSeteUsuario = new \Db\Sete\SeteUsuarios();
        return $dbSeteUsuario->getUsuariosLiberadosSistemaByCidade($codigoCidade, $busca);
    }
    
    public function validarTrocaSenhaUsuario($arPost){
        $boValidate = true;
        $arErros = [];
        if (empty($arPost->id_usuario)) {
            $boValidate = false;
            $arErros[] = "O parâmetro id_usuario é obrigatório!";
        }
        if (empty($arPost->senha_atual) || !$this->isValidMd5($arPost->senha_atual)) {
            $boValidate = false;
            $arErros[] = "O parâmetro senha_atual deve ser um hash md5!";
        }
        if (empty($arPost->nova_senha) || !$this->isValidMd5($arPost->nova_senha)) {
            $boValidate = false;
            $arErros[] = "O parâmetro nova_senha deve ser um hash md5!";
        }
        return ['result' => $boValidate, 'messages' => $arErros];
    }
}