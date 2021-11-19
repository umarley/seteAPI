<?php

namespace Sete\V1\Rest\Fornecedores;

use Db\SetePG\GlbMarcasFornecedores;
use Laminas\Validator\NotEmpty;
use phpDocumentor\Reflection\PseudoTypes\False_;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FornecedoresModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteFornecedores();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row) {
            /*$arDados[$key]['ramo_mecanica'] = ($row['ramo_mecanica'] == 'N' ? "Não" : "Sim");
            $arDados[$key]['ramo_combustivel'] = ($row['ramo_combustivel'] == 'N' ? "Não" : "Sim");
            $arDados[$key]['ramo_seguro'] = ($row['ramo_seguro'] == 'N' ? "Não" : "Sim");*/
            $arDados[$key]['_links']['_self'] = $urlHelper->baseUrl("fornecedores/{$codigoMunicipio}/{$row['id_fornecedor']}");
        }
        return $arDados;
    }

    public function getById($codigoCidade, $idFornecedor) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_fornecedor'] = $idFornecedor;
        $arRow = $this->_entity->getById($arIds);
        $urlHelper = new \Application\Utils\UrlHelper();
        $arRow['_links']['_self'] = $urlHelper->baseUrl("fornecedor/{$codigoCidade}/{$idFornecedor}");
        return $arRow;
    }

    public function prepareInsert($arPost) {
        $arPost = (Array) $arPost;
        $arPost['ramo_mecanica'] = isset($arPost['ramo_mecanica']) ? $arPost['ramo_mecanica'] : 'N';
        $arPost['ramo_combustivel'] = isset($arPost['ramo_combustivel']) ? $arPost['ramo_combustivel'] : 'N';
        $arPost['ramo_seguro'] = isset($arPost['ramo_seguro']) ? $arPost['ramo_seguro'] : 'N';
        $arPost['loc_latitude'] = isset($arPost['loc_latitude']) ? $arPost['loc_latitude'] : null;
        $arPost['loc_longitude'] = isset($arPost['loc_longitude']) ? $arPost['loc_longitude'] : null;
        $arPost['loc_endereco'] = isset($arPost['loc_endereco']) ? $arPost['loc_endereco'] : null;
        $arPost['loc_cep'] = isset($arPost['loc_cep']) ? $arPost['loc_cep'] : null;
        $arPost['dt_criacao'] = date("Y-m-d H:i:s");
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
        if (!isset($arPost['cnpj']) || empty($arPost['cnpj'])) {
            $boValidate = false;
            $arErros['cnpj'] = "O cnpj do Fornecedor deve ser informado!";
        } else {
            $cnpjValido = \Application\Utils\Utils::validarCnpj($arPost['cnpj']);
            $cpfValido = \Application\Utils\Utils::validarCpf($arPost['cnpj']);
            if (!$cnpjValido && !$cpfValido) {
                $boValidate = false;
                $arErros['cnpj'] = "O CNPJ ou CPF informado é inválido!";
            } else {
                $dbSeteFornecedores = new \Db\SetePG\SeteFornecedores();
                $arIds['codigo_cidade'] = $arPost['codigo_cidade'];
                $arIds['cnpj'] = $arPost['cnpj'];
                $fornecedorJaExisteNaCidade = $dbSeteFornecedores->fornecedorExisteParaCidade($arIds);
                if ($fornecedorJaExisteNaCidade) {
                    $boValidate = false;
                    $arErros['cnpj'] = "O CNPJ ou CPF informado já está cadastrado no sistema!";
                }
            }
        }
        if (!isset($arPost['nome']) || empty($arPost['nome'])) {
            $boValidate = false;
            $arErros['nome'] = "O nome do fornecedor deve ser informado!";
        }
        if (!isset($arPost['ramo_mecanica']) && !isset($arPost['ramo_combustivel']) && !isset($arPost['ramo_seguro'])) {
            $boValidate = false;
            $arErros['servicos'] = "Ao menos um serviço deve ser marcado!";
        }

        if ($boValidate) {
            return $this->validarParametrosInsertFornecedores($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    private function validarParametrosInsertFornecedores($arPost) {
        $boValidate = true;
        $arErros = [];
        $arValoresBooleanos = ['S', 'N'];

        if (isset($arPost['ramo_mecanica']) && !in_array($arPost['ramo_mecanica'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['ramo_mecanica'] = "O o valor do objeto ramo_mecanica deve ser S ou N";
        }
        if (isset($arPost['ramo_combustivel']) && !in_array($arPost['ramo_combustivel'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['ramo_combustivel'] = "O o valor do objeto ramo_combustivel deve ser S ou N";
        }
        if (isset($arPost['ramo_seguro']) && !in_array($arPost['ramo_seguro'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['ramo_seguro'] = "O o valor do objeto ramo_seguro deve ser S ou N";
        }

        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function validarUpdate($arPost, $idFornecedor) {
        $arPost = (Array) $arPost;
        $boValidate = true;
        $arErros = [];
        if (!isset($arPost['cnpj']) || empty($arPost['cnpj'])) {
            $boValidate = false;
            $arErros['cnpj'] = "O cnpj do Fornecedor deve ser informado!";
        } else {
            $cnpjValido = \Application\Utils\Utils::validarCnpj($arPost['cnpj']);
            $cpfValido = \Application\Utils\Utils::validarCpf($arPost['cnpj']);
            if (!$cnpjValido && !$cpfValido) {
                $boValidate = false;
                $arErros['cnpj'] = "O CNPJ ou CPF informado é inválido!";
            }
        }
        if (!isset($arPost['nome']) || empty($arPost['nome'])) {
            $boValidate = false;
            $arErros['nome'] = "O nome do fornecedor deve ser informado!";
        }
        if (!isset($arPost['ramo_mecanica']) && !isset($arPost['ramo_combustivel']) && !isset($arPost['ramo_seguro'])) {
            $boValidate = false;
            $arErros['servicos'] = "Ao menos um serviço deve ser marcado!";
        }

        if ($boValidate) {
            return $this->validarParametrosInsertFornecedores($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    public function prepareUpdate($codigoCidade, $idFornecedor, $arPost) {
        $arPost = (Array) $arPost;
        unset($arPost['codigo_cidade']);
        unset($arPost['id_fornecedor']);
        $arPost['dt_alteracao'] = date("Y-m-d H:i:s");
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['id_fornecedor'] = $idFornecedor;
        $arResult = $this->_entity->_atualizar($arId, $arPost);
        return $arResult;
    }

    public function removerRegistroById($codigoCidade, $idFornecedor) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_fornecedor'] = $idFornecedor;
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
