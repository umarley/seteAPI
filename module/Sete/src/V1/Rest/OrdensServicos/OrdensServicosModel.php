<?php

namespace Sete\V1\Rest\OrdensServicos;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OrdensServicosModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteOrdensServicos();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row){
            $arDados[$key]['_links']['_self'] = $urlHelper->baseUrl("ordens-servicos/{$codigoMunicipio}/{$row['id_ordem']}");
        }
        return $arDados;
    }

    public function getById($codigoCidade, $idOrdem) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_ordem'] = $idOrdem;
        $arRow = $this->_entity->getById($arIds);
        $urlHelper = new \Application\Utils\UrlHelper();
        $arRow['_links']['_self'] = $urlHelper->baseUrl("ordensdeservicos/{$codigoCidade}/{$idOrdem}");
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
        if (!isset($arPost['id_fornecedor']) || empty($arPost['id_fornecedor'])) {
            $boValidate = false;
            $arErros['id_fornecedor'] = "O id do fornecedor deve ser informado!";
        }else 
            if (!$this->_entity->fornecedorExisteById($arPost['id_fornecedor'],$arPost['codigo_cidade'])){
                $boValidate = false;
                $arErros['id_fornecedor'] = "O id do fornecedor não está cadastrado!";
            } 
        if (!isset($arPost['id_veiculo']) || empty($arPost['id_veiculo'])) {
            $boValidate = false;
            $arErros['id_veiculo'] = "O id do veículo deve ser informada!";
        } 
        else 
            if (!$this->_entity->veiculoExisteById($arPost['id_veiculo'],$arPost['codigo_cidade'])){
                $boValidate = false;
                $arErros['id_veiculo'] = "O id do veículo não está cadastrado!";
            }
        if (!isset($arPost['data']) || empty($arPost['data'])) {
            $boValidate = false;
            $arErros['data'] = "A data deve ser informada!";
        } else {
            if (!\Application\Utils\Utils::ValidaDataDDMMYYYY($arPost['data'])) {
                $boValidate = false;
                $arErros['data'] = "A data informada é inválida!";
            }
        }
        if (!isset($arPost['tipo_servico']) || empty($arPost['tipo_servico'])) {
            $boValidate = false;
            $arErros['tipo_servico'] = "O tipo de serviço deve ser informado!";

        } else if (!is_numeric($arPost['tipo_servico'])){
            $boValidate = false;
            $arErros['tipo_servico'] = "O serviço deve ser um numeral!";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertOrdensServicos($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    private function validarParametrosInsertOrdensServicos($arPost) {
        $boValidate = true;
        $arErros = [];
        if (isset($arPost['tipo_servico']) && !in_array($arPost['tipo_servico'], \Db\Enum\TipoServico::TIPO_SERVICO)) {
            $boValidate = false;
            $arErros['tipo_servico'] = "O valor do objeto tipo de servico está inválido. Verifique e tente novamente!";
        }
        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function validarUpdate($arPost, $arId) {
        $arPost = (Array) $arPost;
        $boValidate = true;
        $arErros = [];
        $arRow = $this->_entity->getById($arId);
        if(empty($arRow)){
            $boValidate = false;
            $arErros['id_ordem'] = "O id informado não existe!";
        }
        if (!isset($arPost['id_fornecedor']) || empty($arPost['id_fornecedor'])) {
            $boValidate = false;
            $arErros['id_fornecedor'] = "O id do fornecedor deve ser informado!";
        }else 
            if (!$this->_entity->fornecedorExisteById($arPost['id_fornecedor'],$arPost['codigo_cidade'])){
                $boValidate = false;
                $arErros['id_fornecedor'] = "O id do fornecedor não está cadastrado!";
            }
        if (!isset($arPost['id_veiculo']) || empty($arPost['id_veiculo'])) {
            $boValidate = false;
            $arErros['id_veiculo'] = "O id do veículo deve ser informada!";
        } 
        else 
            if (!$this->_entity->veiculoExisteById($arPost['id_veiculo'],$arPost['codigo_cidade'])){
                $boValidate = false;
                $arErros['id_veiculo'] = "O id do veículo não está cadastrado!";
            }
        if (!isset($arPost['data']) || empty($arPost['data'])) {
            $boValidate = false;
            $arErros['data'] = "A data deve ser informada!";
        } else {
            if (!\Application\Utils\Utils::ValidaDataDDMMYYYY($arPost['data'])) {
                $boValidate = false;
                $arErros['data'] = "A data informada é inválida!";
            }
        }
        if (!isset($arPost['tipo_servico']) || empty($arPost['tipo_servico'])) {
            $boValidate = false;
            $arErros['tipo_servico'] = "O tipo de serviço deve ser informado!";

        } else if (!is_numeric($arPost['tipo_servico'])){
            $boValidate = false;
            $arErros['tipo_servico'] = "O serviço deve ser um numeral!";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertOrdensServicos($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    public function prepareUpdate($codigoCidade, $idOrdensServicos, $arPost) {
        $arPost = (Array) $arPost;
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['id_ordem'] = $idOrdensServicos;
        $arResult = $this->_entity->_atualizar($arId, $arPost);
        return $arResult;
    }

    public function removerRegistroById($codigoCidade, $idAluno) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_aluno'] = $idAluno;
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
