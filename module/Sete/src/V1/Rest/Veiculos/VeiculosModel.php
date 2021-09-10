<?php

namespace Sete\V1\Rest\Veiculos;

use phpDocumentor\Reflection\PseudoTypes\False_;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VeiculosModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteVeiculos();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row){
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
        $arPost['ano'] = isset($arPost['ano']) ? $arPost['ano'] : '0';
        $arPost['km_inicial'] = isset($arPost['km_inicial']) ? $arPost['km_inicial'] : '0';
        $arPost['km_atual'] = isset($arPost['km_atual']) ? $arPost['km_atual'] : '0';
        $arPost['capacidade'] = isset($arPost['capacidade']) ? $arPost['capacidade'] : '0';
        $arResult = $this->_entity->_inserir($arPost);
        if ($arResult['result']) {
            $arResult['messages']['id'] = $this->_entity->getUltimoIdInserido();
        }

        return $arResult;
    }

    public function validarInsert($arPost) {
        $regex = '/[A-Z]{3}[0-9][0-9A-Z][0-9]{2}/';
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
        if ( !isset($arPost['placa']) || empty($arPost['placa']) ) {
            $boValidate = false;
            $arErros['placa'] = "A placa do veiculo deve ser informada!";
        }else{
            if($this->_entity->veiculoExiste($arPost['placa'], $arPost['codigo_cidade'])){
                $boValidate = false;
                $arErros['placa'] = "A placa informada já está cadastrada. Verifique e tente novamente!";
            }
            if($arPost['modo']===1){
                if (preg_match($regex, $arPost['placa']) != 1) {
                $boValidate = false;
                $arErros['placa'] = "A placa do veiculo é invalida!";
                }
            }
        }

        if (!isset($arPost['modelo']) || empty($arPost['modelo'])) {
            $boValidate = false;
            $arErros['modelo'] = "O modelo do veiculo deve ser informado!";
        }
        if (!isset($arPost['modo']) || empty($arPost['modo'])) {
            $boValidate = false;
            $arErros['modo'] = "O modo do veiculo deve ser informado!";
        }
        if (!isset($arPost['origem']) || empty($arPost['origem'])) {
            $boValidate = false;
            $arErros['origem'] = "A origem do veiculo deve ser informada!";
        }
        if (!isset($arPost['tipo']) || empty($arPost['tipo'])) {
            $boValidate = false;
            $arErros['tipo'] = "O tipo do veiculo deve ser informado!";
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
        if (isset($arPost['modo']) && !in_array($arPost['modo'], \Db\Enum\ModoVeiculo::MODO_VEICULO)) {
            $boValidate = false;
            $arErros['modo'] = "O valor do objeto modo está inválido. Verifique e tente novamente!";
        }

        //Testando a origem
        if (isset($arPost['origem']) && !in_array($arPost['origem'], \Db\Enum\Origem::ORIGEM)) {
            $boValidate = false;
            $arErros['origem'] = "O valor do objeto origem está inválido. Verifique e tente novamente!";
        }

        //Testando a tipo
        if (isset($arPost['tipo']) && !in_array($arPost['tipo'], \Db\Enum\TipoVeiculo::TIPO_VEICULO)) {
            $boValidate = false;
            $arErros['tipo'] = "O valor do objeto tipo está inválido. Verifique e tente novamente!";
        }

        
        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function validarUpdate($arPost, $idAluno) {
        $arPost = (Array) $arPost;
        $boValidate = true;
        $arErros = [];
        if (!isset($arPost['nome']) || empty($arPost['nome'])) {
            $boValidate = false;
            $arErros['nome'] = "O nome do aluno deve ser informado!";
        }
        if (isset($arPost['cpf']) && !empty($arPost['cpf'])) {
            $cpfValido = \Application\Utils\Utils::validarCpf($arPost['cpf']);
            $dbAluno = new \Db\SetePG\SeteAlunos();
            if (!$cpfValido) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado é inválido!";
            }
            if ($dbAluno->alunoExiste($arPost['cpf'], $idAluno)) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado já existe!";
            }
        }
        if (!isset($arPost['data_nascimento']) || empty($arPost['data_nascimento'])) {
            $boValidate = false;
            $arErros['data_nascimento'] = "O campo data de nascimento deve ser informado!";
        } else {
            if (!\Application\Utils\Utils::ValidaDataDDMMYYYY($arPost['data_nascimento'])) {
                $boValidate = false;
                $arErros['data_nascimento'] = "A data informada é inválida!";
            }
        }
        if (!isset($arPost['nome_responsavel']) || empty($arPost['nome_responsavel'])) {
            $boValidate = false;
            $arErros['nome_responsavel'] = "O nome do responsável pelo aluno deve ser informado!";
        }
        if (!isset($arPost['grau_responsavel']) || $arPost['grau_responsavel'] === "") {
            $boValidate = false;
            $arErros['grau_responsavel'] = "Informe o grau de parentesco do responsável pelo aluno!";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertVeiculo($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    public function prepareUpdate($codigoCidade, $idAluno, $arPost) {
        $arPost = (Array) $arPost;
        unset($arPost['codigo_cidade']);
        unset($arPost['id_aluno']);
        $arPost['da_porteira'] = isset($arPost['da_porteira']) ? $arPost['da_porteira'] : 'N';
        $arPost['da_mataburro'] = isset($arPost['da_mataburro']) ? $arPost['da_mataburro'] : 'N';
        $arPost['da_colchete'] = isset($arPost['da_colchete']) ? $arPost['da_colchete'] : 'N';
        $arPost['da_atoleiro'] = isset($arPost['da_atoleiro']) ? $arPost['da_atoleiro'] : 'N';
        $arPost['da_ponterustica'] = isset($arPost['da_ponterustica']) ? $arPost['da_ponterustica'] : 'N';
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['id_aluno'] = $idAluno;
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
