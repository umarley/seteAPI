<?php

namespace Sete\V1\Rest\Alunos;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AlunosModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteAlunos();
    }

    public function getAll($codigoMunicipio) {
        $arDados = $this->_entity->getLista($codigoMunicipio);
        return $arDados;
    }

    public function getById($codigoCidade, $idAluno) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_aluno'] = $idAluno;
        $arRow = $this->_entity->getById($arIds);
        if (!empty($arRow)) {
            $arRow['data_nascimento'] = date("d/m/Y", strtotime($arRow['data_nascimento']));
        }
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
            $dbAluno = new \Db\SetePG\SeteAlunos();
            if (!$cpfValido) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado é inválido!";
            }
            if ($dbAluno->alunoExiste($arPost['cpf'])) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado já existe!";
            }
        }
        if (!isset($arPost['data_nascimento']) || empty($arPost['data_nascimento'])) {
            $boValidate = false;
            $arErros['data_nascimento'] = "O campo data de nascimento deve ser informado!";
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
            return $this->validarParametrosInsertAluno($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    private function validarParametrosInsertAluno($arPost) {
        $boValidate = true;
        $arErros = [];
        $arValoresBooleanos = ['S', 'N'];
        if (isset($arPost['da_porteira']) && !in_array($arPost['da_porteira'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_porteira'] = "O o valor do objeto da_porteira deve ser S ou N";
        }
        if (isset($arPost['da_mataburro']) && !in_array($arPost['da_mataburro'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_mataburro'] = "O o valor do objeto da_mataburro deve ser S ou N";
        }
        if (isset($arPost['da_colchete']) && !in_array($arPost['da_colchete'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_colchete'] = "O o valor do objeto da_colchete deve ser S ou N";
        }
        if (isset($arPost['da_atoleiro']) && !in_array($arPost['da_atoleiro'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_atoleiro'] = "O o valor do objeto da_atoleiro deve ser S ou N";
        }
        if (isset($arPost['da_ponterustica']) && !in_array($arPost['da_ponterustica'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_ponterustica'] = "O o valor do objeto da_ponterustica deve ser S ou N";
        }
        if (isset($arPost['def_caminhar']) && !in_array($arPost['def_caminhar'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['def_caminhar'] = "O o valor do objeto def_caminhar deve ser S ou N";
        }
        if (isset($arPost['def_ouvir']) && !in_array($arPost['def_ouvir'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['def_ouvir'] = "O o valor do objeto def_ouvir deve ser S ou N";
        }
        if (isset($arPost['def_enxergar']) && !in_array($arPost['def_enxergar'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['def_enxergar'] = "O o valor do objeto def_enxergar deve ser S ou N";
        }
        if (isset($arPost['def_mental']) && !in_array($arPost['def_mental'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['def_mental'] = "O o valor do objeto def_mental deve ser S ou N";
        }
        if (isset($arPost['sexo']) && !in_array($arPost['sexo'], \Db\Enum\Sexo::SEXOS)) {
            $boValidate = false;
            $arErros['sexo'] = "O valor do objeto sexo está inválido. Verifique e tente novamente!";
        }
        if (isset($arPost['cor']) && !in_array($arPost['cor'], \Db\Enum\CorRaca::COR_RACA)) {
            $boValidate = false;
            $arErros['cor'] = "O valor do objeto cor está inválido. Verifique e tente novamente!";
        }
        if (isset($arPost['turno']) && !in_array($arPost['turno'], \Db\Enum\Turno::TURNO)) {
            $boValidate = false;
            $arErros['turno'] = "O valor do objeto turno está inválido. Verifique e tente novamente!";
        }

        if (isset($arPost['nivel']) && !in_array($arPost['nivel'], \Db\Enum\NivelAluno::NIVEL)) {
            $boValidate = false;
            $arErros['nivel'] = "O valor do objeto nivel está inválido. Verifique e tente novamente!";
        }
        if (isset($arPost['grau_responsavel']) && !in_array($arPost['grau_responsavel'], \Db\Enum\GrauParentesco::GRAU_PARENTESCO)) {
            $boValidate = false;
            $arErros['grau_responsavel'] = "O valor do objeto grau_responsavel está inválido. Verifique e tente novamente!";
        }
        if (isset($arPost['mec_tp_localizacao']) && !in_array($arPost['mec_tp_localizacao'], \Db\Enum\MecTpLocalizacao::LOCALIZACAO)) {
            $boValidate = false;
            $arErros['mec_tp_localizacao'] = "O valor do objeto mec_tp_localizacao está inválido. Verifique e tente novamente!";
        }

        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function validarUpdate($arPost) {
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
            if ($dbAluno->alunoExiste($arPost['cpf'])) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado já existe!";
            }
        }
        if (!isset($arPost['data_nascimento']) || empty($arPost['data_nascimento'])) {
            $boValidate = false;
            $arErros['data_nascimento'] = "O campo data de nascimento deve ser informado!";
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
            return $this->validarParametrosInsertAluno($arPost);
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
    
    public function removerRegistroById($codigoCidade, $idAluno){
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
