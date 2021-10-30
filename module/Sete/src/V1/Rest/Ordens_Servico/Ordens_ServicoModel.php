<?php

namespace Sete\V1\Rest\Ordens_Servico;

use Laminas\Validator\NotEmpty;
use phpDocumentor\Reflection\PseudoTypes\False_;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Ordens_ServicoModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteOrdensServico();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row){
            $arDados[$key]['tipo'] = \Db\Enum\TipoVeiculo::getLabel($row['tipo']);
            $arDados[$key]['origem'] = \Db\Enum\Origem::getLabel($row['origem']); 
            $arDados[$key]['manutencao'] = ($row['manutencao'] == 'N' ? "Não" : "Sim");
            if(!empty($row['marca'])){
                $arDados[$key]['marca'] = $dbGlbMarcas->getNomeById($row['marca']);
            }

            $arDados[$key]['_links']['_self'] = $urlHelper->baseUrl("veiculos/{$codigoMunicipio}/{$row['id_veiculo']}");
        }
        return $arDados;
    }

    public function getById($codigoCidade, $idVeiculo) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_veiculo'] = $idVeiculo;
        $arRow = $this->_entity->getById($arIds);
        $urlHelper = new \Application\Utils\UrlHelper();
        $arRow['_links']['_self'] = $urlHelper->baseUrl("veiculos/{$codigoCidade}/{$idVeiculo}");
        return $arRow;
    }

    public function prepareInsert($arPost) {
        $arPost = (Array) $arPost;
        $arPost['comentario'] = isset($arPost['comentario']) ? $arPost['comentario'] : 'Sem comentários.';
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
        if ( !isset($arPost['data']) || empty($arPost['data']) ) {
            $boValidate = false;
            $arErros['data'] = "A data deve ser informada!";
        }
        if (!isset($arPost['tipo_servico']) || empty($arPost['tipo_servico'])) {
            $boValidate = false;
            $arErros['tipo_servico'] = "O tipo_servico deve ser informado!";
        }
        
        if ($boValidate) {
            return $this->validarParametrosInsertVeiculo($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    private function validarParametrosInsertVeiculo($arPost) {
        $boValidate = true;
        $arErros = [];

        //Testando o modo
        if (isset($arPost['data'])) {
            $data = explode("/",$arPost['data']);
	        $d = $data[0];
	        $m = $data[1];
            $y = $data[2];
            $res = checkdate($m,$d,$y);
            if ($res != 1){
                $boValidate = false;
                $arErros['data'] = "O valor de data foi inserido de maneira erronea, deve estar como DIA/MÊS/ANO. Verifique e tente novamente!";
            }
        }
        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function validarUpdate($arPost, $idVeiculo) {
        $arPost = (Array) $arPost;
        $codigoCidade = $arPost['codigo_cidade'];
        $boValidate = true;
        $arErros = [];
        if (isset($arPost['placa']) && !empty($arPost['placa'])) {
            $placaValida = \Application\Utils\Utils::validarPlaca($arPost['placa']);
            $dbVeiculo = new \Db\SetePG\SeteVeiculos();
            if (!$placaValida) {
                $boValidate = false;
                $arErros['placa'] = "A placa informada é inválida!";
            }
            if ($dbVeiculo->veiculoExisteUnico($arPost['placa'], $codigoCidade , $idVeiculo)) {
                $boValidate = false;
                $arErros['placa'] = "A placa informada já existe!";
            }
            
        }else{
            $boValidate = false;
            $arErros['placa'] = "A placa do veiculo deve ser informado!";
        }
        if (!isset($arPost['modelo']) || empty($arPost['modelo'])) {
            $boValidate = false;
            $arErros['modelo'] = "O modelo do veiculo deve ser informado!";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertVeiculo($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    public function prepareUpdate($codigoCidade, $idVeiculo, $arPost) {
        $arPost = (Array) $arPost;
        unset($arPost['codigo_cidade']);
        unset($arPost['id_veiculo']);
        $arPost['ano'] = isset($arPost['ano']) ? $arPost['ano'] : '0';
        $arPost['modo'] = isset($arPost['modo']) ? $arPost['modo'] : '1';
        $arPost['origem'] = isset($arPost['origem']) ? $arPost['origem'] : '1';
        $arPost['km_inicial'] = isset($arPost['km_inicial']) ? $arPost['km_inicial'] : '0';
        $arPost['km_atual'] = isset($arPost['km_atual']) ? $arPost['km_atual'] : '0';
        $arPost['capacidade'] = isset($arPost['capacidade']) ? $arPost['capacidade'] : '0';
        $arPost['tipo'] = isset($arPost['tipo']) ? $arPost['tipo'] : '1';
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['id_veiculo'] = $idVeiculo;
        $arResult = $this->_entity->_atualizar($arId, $arPost);
        return $arResult;
    }

    public function removerRegistroById($codigoCidade, $idVeiculo) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_veiculo'] = $idVeiculo;
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
