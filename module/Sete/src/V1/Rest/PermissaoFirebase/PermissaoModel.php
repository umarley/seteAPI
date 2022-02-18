<?php

namespace Sete\V1\Rest\PermissaoFirebase;

use Application\Utils\Utils;

class PermissaoModel {

    protected $_entity;

    public function __construct() {
        
    }

    public function getAll() {
        return [];
    }

    public function getById($codigo) {
        return [];
    }

    public function validarPOST($arDados) {
        $boValidate = true;
        $arErros = [];
        $enumTipoPermissao = ['admin', 'reader'];
        if (empty($arDados->email)) {
            $boValidate = false;
            $arErros[] = "o Email deve ser informado.";
        } else if (!Utils::validarEmail($arDados->email)) {
            $boValidate = false;
            $arErros[] = "o Email informado é inválido! Verifique e tente novamente.";
        }
        if (!in_array($arDados->tipo_permissao, $enumTipoPermissao)) {
            $boValidate = false;
            $arErros[] = "o Tipo da permissão deve ser admin ou reader.";
        }
        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function processarPermissaoFirebase($arDados, $accessToken) {
        $dbSetePGUsuarios = new \Db\SetePG\SeteUsuarios();               
        $findEmail = $dbSetePGUsuarios->usuarioExisteByEmail($arDados->email);
        if ($findEmail) {
            $arUsuario = $dbSetePGUsuarios->getUsuarioByUsername($arDados->email);
            if($arUsuario['is_liberado'] === 'S'){
                return ['resposta' => ['result' => false, 'messages' => "Usuário já se encontra liberado para usar o sistema!"], 'codeHTTP' => 200];
            }else{
                $arResult = $dbSetePGUsuarios->_liberarUsuario($arDados->email, $arDados->tipo_permissao);
                return ['resposta' => ['result' => $arResult['result'], 'messages' => $arResult['messages']], 'codeHTTP' => 201];
            }
        } else {
            return ['resposta' => ['result' => false, 'messages' => "Email não encontrado no firestore!"], 'codeHTTP' => 404];
        }
    }

    private function liberarUsuarioFirebaseColecaoConfig(\Application\Model\FirebaseModel $dbModelFirebase, $arUsuarioFirestore, $arRequisicao, $documentoFirestore, $accessToken) {
        $dbSeteUsuariosLiberados = new \Db\Sete\SeteUsuariosLiberados();
        $dbAPILiberacaoUsuario = new \Db\API\ApiLiberacaoUsuario();
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $uidUsuario = key($arUsuarioFirestore);
        if (!in_array($uidUsuario, $documentoFirestore['users'])) {
            ($arRequisicao->tipo_permissao === 'admin') ? array_push($documentoFirestore['admin'], $uidUsuario) : array_push($documentoFirestore['readers'], $uidUsuario);
            array_push($documentoFirestore['users'], $uidUsuario);
            $dbModelFirebase->setDocumentoColecaoConfig($arUsuarioFirestore[$uidUsuario]['COD_CIDADE'], $documentoFirestore);
            $dbSeteUsuariosLiberados->_inserir(['uid' => $uidUsuario, 'type' => 'users']);
            $dbAPILiberacaoUsuario->_inserir(['uid' => $uidUsuario, 'dt_liberacao' => date("Y-m-d H:i:s"), 'criado_por' => $dbCoreAccessToken->getEmailUsuarioByAccessToken($accessToken)]);
            return ['resposta' => ['result' => true, 'messages' => "Email incluido na lista de acesso!"], 'codeHTTP' => 201];
        } else {
            return ['resposta' => ['result' => false, 'messages' => "Usuário já com o acesso liberado!"], 'codeHTTP' => 200];
        }
    }

    private function criarDocumentoComCamposColecaoConfig(\Application\Model\FirebaseModel $dbModelFirebase, $arUsuarioFirestore, $arRequisicao, $accessToken) {
        $dbSeteUsuariosLiberados = new \Db\Sete\SeteUsuariosLiberados();
        $dbAPILiberacaoUsuario = new \Db\API\ApiLiberacaoUsuario();
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $uidUsuario = key($arUsuarioFirestore);
        $codigoCidade = $arUsuarioFirestore[$uidUsuario]['COD_CIDADE'];
        $arNovoDocumento = [
            'admin' => [],
            'readers' => [],
            'users' => []
        ];
        ($arRequisicao->tipo_permissao === 'admin') ? array_push($arNovoDocumento['admin'], $uidUsuario) : array_push($arNovoDocumento['readers'], $uidUsuario);
        array_push($arNovoDocumento['users'], $uidUsuario);
        $dbModelFirebase->setDocumentoColecaoConfig($codigoCidade, $arNovoDocumento);
        $dbSeteUsuariosLiberados->_inserir(['uid' => $uidUsuario, 'type' => 'users']);
        $dbAPILiberacaoUsuario->_inserir(['uid' => $uidUsuario, 'dt_liberacao' => date("Y-m-d H:i:s"), 'criado_por' => $dbCoreAccessToken->getEmailUsuarioByAccessToken($accessToken)]);
        return ['resposta' => ['result' => true, 'messages' => "Email incluido na lista de acesso!"], 'codeHTTP' => 201];
    }
    
    public function getUsuariosLiberar($pagina, $busca = ""){
        $dbSeteUsuario = new \Db\SetePG\SeteUsuarios();
        $qtdPerPage = 20;
        $totalRegistros = $dbSeteUsuario->getTotalUsuariosPendentesLiberacao($busca);
        $qtdPaginas = ceil($totalRegistros / $qtdPerPage);
        $offset = ($qtdPerPage * $pagina) - $qtdPerPage;
        $arData = $dbSeteUsuario->getUsuariosPendentesLiberacao($offset, $qtdPerPage, $busca);
        return [
            'qtd_registros' => (int) $totalRegistros, 
            'pages' => (int) $qtdPaginas, 
            'reg_por_pagina' => (int) $qtdPerPage,
            'pg_atual' => (int) $pagina, 
            'registros' => $arData
          ];
    }
    
    public function excluirUsuarioNaoLiberado($codigoCidade, $idUsuario){
        $dbSetePGUsuarios = new \Db\SetePG\SeteUsuarios();
        return $dbSetePGUsuarios->_delete(['codigo_cidade' => $codigoCidade, 'id_usuario' => $idUsuario]);
    }

}
