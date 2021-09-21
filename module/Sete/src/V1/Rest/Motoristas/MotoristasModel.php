<?php

namespace Sete\V1\Rest\Motoristas;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MotoristasModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteMotoristas();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row){
            $arDados[$key]['_links']['_self'] = $urlHelper->baseUrl("alunos/{$codigoMunicipio}/{$row['id_aluno']}");
        }
        return $arDados;
    }

    public function getById($codigoCidade, $idAluno) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_aluno'] = $idAluno;
        $arRow = $this->_entity->getById($arIds);
        if (!empty($arRow)) {
            $arRow['data_nascimento'] = date("d/m/Y", strtotime($arRow['data_nascimento']));
        }
        $urlHelper = new \Application\Utils\UrlHelper();
        $arRow['_links']['_self'] = $urlHelper->baseUrl("alunos/{$codigoCidade}/{$idAluno}/escola");
        return $arRow;
    }

    public function prepareInsert($arPost) {
        $arPost = (Array) $arPost;
        $arPost['da_porteira'] = isset($arPost['da_porteira']) ? $arPost['da_porteira'] : 'N';
        $arPost['da_mataburro'] = isset($arPost['da_mataburro']) ? $arPost['da_mataburro'] : 'N';
        $arPost['da_colchete'] = isset($arPost['da_colchete']) ? $arPost['da_colchete'] : 'N';
        $arPost['da_atoleiro'] = isset($arPost['da_atoleiro']) ? $arPost['da_atoleiro'] : 'N';
        $arPost['da_ponterustica'] = isset($arPost['da_ponterustica']) ? $arPost['da_ponterustica'] : 'N';
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
            $arErros['nome'] = "O nome do aluno deve ser informado!";
        }
        if (isset($arPost['cpf']) && !empty($arPost['cpf'])) {
            $cpfValido = \Application\Utils\Utils::validarCpf($arPost['cpf']);
            $dbMotorista = new \Db\SetePG\SeteMotoristas();
            if (!$cpfValido) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado é inválido!";
            }
            if ($dbMotorista->motoristaExiste($arPost['cpf'])) {
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
        if ($boValidate) {
            return $this->validarParametrosInsertMotorista($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    private function validarParametrosInsertMotorista($arPost) {
        $boValidate = true;
        $arErros = [];
        $arValoresBooleanos = ['S', 'N'];
        if (isset($arPost['turno_manha']) && !in_array($arPost['turno_manha'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['turno_manha'] = "O o valor do objeto turno_manha deve ser S ou N";
        }
        if (isset($arPost['turno_tarde']) && !in_array($arPost['turno_tarde'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['turno_tarde'] = "O valor do objeto turno_tarde deve ser S ou N";
        }
        if (isset($arPost['turno_noite']) && !in_array($arPost['turno_noite'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['turno_noite'] = "O valor do objeto da_colchete deve ser S ou N";
        }
        if (isset($arPost['tem_cnh_a']) && !in_array($arPost['tem_cnh_a'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['tem_cnh_a'] = "O o valor do objeto tem_cnh_a deve ser S ou N";
        }
        if (isset($arPost['tem_cnh_b']) && !in_array($arPost['tem_cnh_b'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['tem_cnh_b'] = "O o valor do objeto tem_cnh_b deve ser S ou N";
        }
        if (isset($arPost['tem_cnh_c']) && !in_array($arPost['tem_cnh_c'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['tem_cnh_c'] = "O o valor do objeto tem_cnh_c deve ser S ou N";
        }
        if (isset($arPost['tem_cnh_d']) && !in_array($arPost['tem_cnh_d'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['tem_cnh_d'] = "O o valor do objeto tem_cnh_d deve ser S ou N";
        }
        if (isset($arPost['tem_cnh_e']) && !in_array($arPost['tem_cnh_e'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['tem_cnh_e'] = "O o valor do objeto tem_cnh_e deve ser S ou N";
        }
        if (isset($arPost['sexo']) && !in_array($arPost['sexo'], \Db\Enum\Sexo::SEXOS)) {
            $boValidate = false;
            $arErros['sexo'] = "O valor do objeto sexo está inválido. Verifique e tente novamente!";
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
            $dbMotorista = new \Db\SetePG\SeteMotoristas();
            if (!$cpfValido) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado é inválido!";
            }
            if ($dbMotorista->motoristaExiste($arPost['cpf'])) {
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
        if ($boValidate) {
            return $this->validarParametrosInsertMotorista($arPost);
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