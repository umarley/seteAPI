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
            $arErros['nome'] = "O nome do aluno deve ser informado!";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertEscola($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    public function prepareUpdate($codigoCidade, $idEscola, $arPost) {
        $arPost = (Array) $arPost;
        unset($arPost['codigo_cidade']);
        unset($arPost['id_aluno']);
        $arPost = (Array) $arPost;
        $arPost['mec_in_regular'] = isset($arPost['mec_in_regular']) ? $arPost['mec_in_regular'] : 'N';
        $arPost['mec_in_eja'] = isset($arPost['mec_in_eja']) ? $arPost['mec_in_eja'] : 'N';
        $arPost['mec_in_profissionalizante'] = isset($arPost['mec_in_profissionalizante']) ? $arPost['mec_in_profissionalizante'] : 'N';
        $arPost['mec_in_especial_exclusiva'] = isset($arPost['mec_in_especial_exclusiva']) ? $arPost['mec_in_especial_exclusiva'] : 'N';
        $arPost['horario_matutino'] = isset($arPost['horario_matutino']) ? $arPost['horario_matutino'] : 'N';
        $arPost['horario_vespertino'] = isset($arPost['horario_vespertino']) ? $arPost['horario_vespertino'] : 'N';
        $arPost['horario_noturno'] = isset($arPost['horario_noturno']) ? $arPost['horario_noturno'] : 'N';
        $arPost['ensino_superior'] = isset($arPost['ensino_superior']) ? $arPost['ensino_superior'] : 'N';
        $arPost['ensino_medio'] = isset($arPost['ensino_medio']) ? $arPost['ensino_medio'] : 'N';
        $arPost['ensino_fundamental'] = isset($arPost['ensino_fundamental']) ? $arPost['ensino_fundamental'] : 'N';
        $arPost['ensino_pre_escola'] = isset($arPost['ensino_pre_escola']) ? $arPost['ensino_pre_escola'] : 'N';
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['id_escola'] = $idEscola;
        $arResult = $this->_entity->_atualizar($arId, $arPost);
        return $arResult;
    }
    
    public function removerRegistroById($codigoCidade, $idEscola){
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_escola'] = $idEscola;
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
