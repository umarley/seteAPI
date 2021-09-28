<?php

namespace Sete\V1\Rest\Garagens;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GaragensModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteGaragens();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row){
            $arDados[$key]['_links']['_self'] = $urlHelper->baseUrl("garagens/{$codigoMunicipio}/{$row['id_garagem']}");
        }
        return $arDados;
    }

    public function getById($codigoCidade, $idGaragem) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_garagem'] = $idGaragem;
        $arRow = $this->_entity->getById($arIds);
        $urlHelper = new \Application\Utils\UrlHelper();
        $arRow['_links']['_self'] = $urlHelper->baseUrl("garagens/{$codigoCidade}/{$idGaragem}/veiculos");
        return $arRow;
    }

    public function prepareInsert($arPost) {
        $arPost = (Array) $arPost;
        $arResult = $this->_entity->_inserir($arPost);
        if ($arResult['result']) {
            $arResult['messages']['id'] = $this->_entity->getUltimoIdInserido();
        }

        return $arResult;
    }

    public function validarInsert($arPost) {
        $arPost = (Array) $arPost;
        $boValidate = true;
        $arErros = [];
        if (!isset($arPost['codigo_cidade']) || empty($arPost['codigo_cidade'])) {
            $boValidate = false;
            $arErros['codigo_cidade'] = "O código da cidade deve ser informado!";
        } else {
            $dbMunicipio = new \Db\SetePG\GlbMunicipios();
            if (!$dbMunicipio->municipioExiste($arPost['codigo_cidade'])) {
                $boValidate = false;
                $arErros['codigo_cidade'] = "O código da cidade não existe. Verifique e tente novamente!";
            }
        }
        if (!isset($arPost['nome']) || empty($arPost['nome'])) {
            $boValidate = false;
            $arErros['nome'] = "O nome da garagem deve ser informado!";
        }
        if (!isset($arPost['loc_latitude']) || empty($arPost['loc_latitude'])) {
            $boValidate = false;
            $arErros['loc_latitude'] = "A latitude da garagem deve ser informado!";
        }
        if (!isset($arPost['loc_longitude']) || empty($arPost['loc_longitude'])) {
            $boValidate = false;
            $arErros['loc_longitude'] = "A longitude da garagem deve ser informado!";
        }
        if (!isset($arPost['loc_endereco']) || empty($arPost['loc_endereco'])) {
            $boValidate = false;
            $arErros['loc_endereco'] = "O endereço da garagem deve ser informado!";
        }
        if (!isset($arPost['loc_cep']) || empty($arPost['loc_cep'])) {
            $boValidate = false;
            $arErros['loc_cep'] = "O cep da garagem deve ser informado!";
        }
        else{
            $cep = trim($arPost['loc_cep']);
            $avaliaCep = preg_match("/[0-9]{5}-[0-9]{3}/", $cep);
            $avaliaCepSB = preg_match("/[0-9]{8}/", $cep);
            if(!$avaliaCep && !$avaliaCepSB) { 
                $boValidate = false;
                $arErros['loc_cep'] = "O cep informado é inválido!";
            }
        }
        if ($boValidate) {
            $boValidate = true;
            $arErros = [];
            return['result' => $boValidate, 'messages' => $arErros];
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }


    public function validarUpdate($arPost) {
        $arPost = (Array) $arPost;
        $boValidate = true;
        $arErros = [];
        if (!isset($arPost['nome']) || empty($arPost['nome'])) {
            $boValidate = false;
            $arErros['nome'] = "O nome da garagem deve ser informada!";
        }
        if (!isset($arPost['loc_latitude']) || empty($arPost['loc_latitude'])) {
            $boValidate = false;
            $arErros['loc_latitude'] = "A latitude da garagem deve ser informada!";
        }
        if (!isset($arPost['loc_longitude']) || empty($arPost['loc_longitude'])) {
            $boValidate = false;
            $arErros['loc_longitude'] = "A longitude da garagem deve ser informada!";
        }
        if (!isset($arPost['loc_endereco']) || empty($arPost['loc_endereco'])) {
            $boValidate = false;
            $arErros['loc_endereco'] = "O endereco da garagem deve ser informado!";
        }
        if (!isset($arPost['loc_cep']) || empty($arPost['loc_cep'])) {
            $boValidate = false;
            $arErros['loc_cep'] = "O cep da garagem deve ser informado!";
        }
        else{
            $cep = trim($arPost['loc_cep']);
            $avaliaCep = preg_match("/[0-9]{5}-[0-9]{3}/", $cep);
            $avaliaCepSB = preg_match("/[0-9]{8}/", $cep);
            if(!$avaliaCep && !$avaliaCepSB) { 
                $boValidate = false;
                $arErros['loc_cep'] = "O cep informado é inválido!";
            }
        }
        if ($boValidate) {
            return ['result' => $boValidate, 'messages' => $arErros];
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    public function prepareUpdate($codigoCidade, $idGaragem, $arPost) {
        $arPost = (Array) $arPost;
        unset($arPost['codigo_cidade']);
        unset($arPost['id_garagem']);
        $arPost = (Array) $arPost;
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['id_garagem'] = $idGaragem;
        $arResult = $this->_entity->_atualizar($arId, $arPost);
        return $arResult;
    }
    
    public function removerRegistroById($codigoCidade, $idGaragem){
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_garagem'] = $idGaragem;
        $arResult = $this->_entity->_delete($arIds);
        return $arResult;
    }

    public function getListaPaginada($pagina, $busca = "") {
        $qtdPerPage = 20;
        $totalRegistros = $this->_entity->getTotalMunicipios($busca);
        $qtdPaginas = ceil($totalRegistros / $qtdPerPage);
        $offset = ($qtdPerPage * $pagina) - $qtdPerPage;
        $arData = $this->_entity->getMunicipiosLista($offset, $qtdPerPage, $busca);
        return [
            'qtd_registros' => (int) $totalRegistros,
            'pages' => (int) $qtdPaginas,
            'reg_por_pagina' => (int) $qtdPerPage,
            'pg_atual' => (int) $pagina,
            'registros' => $arData
        ];
    }

}
