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

    public function processarPermissaoFirebase($arDados) {
        $dbModelFirebase = new \Application\Model\FirebaseModel();
        $findEmail = $dbModelFirebase->procurarDocumentoUsuarioPorEmail($arDados->email);
        if (!empty($findEmail)) {
            $uidUsuario = key($findEmail);
            $codigoCidade = $findEmail[$uidUsuario]['COD_CIDADE'];
            $documentoFirestore = $dbModelFirebase->getDocumentoByIdConfig($codigoCidade);
            if (!$documentoFirestore) {
                $this->criarDocumentoComCamposColecaoConfig($dbModelFirebase, $findEmail, $arDados);
            } else {
                $this->liberarUsuarioFirebaseColecaoConfig($dbModelFirebase, $findEmail, $arDados, $documentoFirestore);
            }
            return ['resposta' => ['result' => true, 'messages' => "Email incluido na lista de acesso!"], 'codeHTTP' => 201];
        } else {
            return ['resposta' => ['result' => false, 'messages' => "Email não encontrado no firestore!"], 'codeHTTP' => 404];
        }
    }

    private function liberarUsuarioFirebaseColecaoConfig(\Application\Model\FirebaseModel $dbModelFirebase, $arUsuarioFirestore, $arRequisicao, $documentoFirestore) {
        $uidUsuario = key($arUsuarioFirestore);
        if (!in_array($uidUsuario, $documentoFirestore['users'])) {
            ($arRequisicao->tipo_permissao === 'admin') ? array_push($documentoFirestore['admin'], $uidUsuario) : array_push($documentoFirestore['readers'], $uidUsuario);
            array_push($documentoFirestore['users'], $uidUsuario);
            $dbModelFirebase->setDocumentoColecaoConfig($codigoCidade, $documentoFirestore);
        } else {
            return ['resposta' => ['result' => false, 'messages' => "Usuário já com o acesso liberado!"], 'codeHTTP' => 200];
        }
    }

    private function criarDocumentoComCamposColecaoConfig(\Application\Model\FirebaseModel $dbModelFirebase, $arUsuarioFirestore, $arRequisicao) {
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
    }

}
