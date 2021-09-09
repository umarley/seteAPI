<?php
namespace Sete\V1\Rest\Authenticator;

class AuthenticatorModel {
    
    protected $_entity;
    
    public function __construct() {
        $this->_entity = new AuthenticatorEntity();
    }
    
    public function autenticarUsuario($arPost){
        $dbCoreUsuario = new \Db\Core\Usuario();
        if($dbCoreUsuario->checkUsuarioAndPassword($arPost['usuario'], $arPost['senha'])){
            $dbCoreAccessToken = new \Db\Core\AccessToken();
            $arDadosUsuario = $dbCoreUsuario->getUsuarioByUsername($arPost['usuario']);
            $arAccessToken = $dbCoreAccessToken->gerarAccessToken($arDadosUsuario['email']);
            $arResult['result'] = true;
            $arResult['access_token'] = $arAccessToken;
            $arResult['messages'] = "Login efetuado com sucesso!";
        }else{
            $arResult['result'] = false;
            $arResult['status'] =  400;
            $arResult['messages'] = "Usuário / Senha não conferem!";
        }
        return $arResult;
    }
    
    public function validarAccessToken($accessToken){
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $accessTokenValido = $dbCoreAccessToken->accessTokenValido($accessToken);
        if($accessTokenValido){
            return true;
        }else{
            return false;
        }
    }
    
    public function autenticarUsuarioSETE($arPost){
        $dbCoreUsuarioPG = new \Db\SetePG\SeteUsuarios();
        if($dbCoreUsuarioPG->checkUsuarioAndPassword($arPost['usuario'], $arPost['senha'])){
            $dbCoreAccessToken = new \Db\Core\AccessToken();
            $arDadosUsuario = $dbCoreUsuarioPG->getUsuarioByUsername($arPost['usuario']);
            $arAccessToken = $dbCoreAccessToken->gerarAccessTokenUsuarioSETE($arDadosUsuario['email']);
            $arAccessToken['tipo_permissao'] = $arDadosUsuario['nivel_permissao'];
            $arResult['data'] = $dbCoreUsuarioPG->getUsuarioByAccessToken($arAccessToken['access_token']);
            $arResult['result'] = true;
            $arResult['access_token'] = $arAccessToken;
            $arResult['status'] =  201;
            $arResult['messages'] = "Login efetuado com sucesso!";
        }else{
            $arResult['result'] = false;
            $arResult['status'] =  403;
            $arResult['messages'] = "Usuário / Senha não conferem!";
        }
        return $arResult;
    }
    
    
    
}

