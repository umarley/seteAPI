<?php

namespace Sete\V1\Rest\Escolas;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EscolasModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteEscolas();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row) {
            $arDados[$key]['qtd_alunos'] = $this->_entity->qtdAlunosPorEscola($codigoMunicipio, $row['id_escola']);
            $arDados[$key]['_links']['_self'] = $urlHelper->baseUrl("escolas/{$codigoMunicipio}/{$row['id_escola']}");
        }
        return $arDados;
    }

    public function getById($codigoCidade, $idEscola) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_escola'] = $idEscola;
        $arRow = $this->_entity->getById($arIds);
        $urlHelper = new \Application\Utils\UrlHelper();
        $arRow['_links']['_self'] = $urlHelper->baseUrl("escolas/{$codigoCidade}/{$idEscola}/alunos");
        return $arRow;
    }

    public function prepareInsert($arPost) {
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
            $arErros['nome'] = "O nome da escola deve ser informado!";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertEscola($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    private function validarParametrosInsertEscola($arPost) {
        $boValidate = true;
        $arErros = [];
        $arValoresBooleanos = ['S', 'N'];
        if (isset($arPost['mec_in_regular']) && !in_array($arPost['mec_in_regular'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['mec_in_regular'] = "O valor do objeto mec_in_regular deve ser S ou N";
        }
        if (isset($arPost['mec_in_eja']) && !in_array($arPost['mec_in_eja'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['mec_in_eja'] = "O valor do objeto mec_in_eja deve ser S ou N";
        }
        if (isset($arPost['mec_in_profissionalizante']) && !in_array($arPost['mec_in_profissionalizante'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['mec_in_profissionalizante'] = "O valor do objeto mec_in_profissionalizante deve ser S ou N";
        }
        if (isset($arPost['mec_in_especial_exclusiva']) && !in_array($arPost['mec_in_especial_exclusiva'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['mec_in_especial_exclusiva'] = "O valor do objeto mec_in_especial_exclusiva deve ser S ou N";
        }
        if (isset($arPost['horario_matutino']) && !in_array($arPost['horario_matutino'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['horario_matutino'] = "O valor do objeto horario_matutino deve ser S ou N";
        }
        if (isset($arPost['horario_vespertino']) && !in_array($arPost['horario_vespertino'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['horario_vespertino'] = "O valor do objeto horario_vespertino deve ser S ou N";
        }
        if (isset($arPost['horario_noturno']) && !in_array($arPost['horario_noturno'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['horario_noturno'] = "O valor do objeto horario_noturno deve ser S ou N";
        }
        if (isset($arPost['ensino_superior']) && !in_array($arPost['ensino_superior'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['ensino_superior'] = "O valor do objeto ensino_superior deve ser S ou N";
        }
        if (isset($arPost['ensino_medio']) && !in_array($arPost['ensino_medio'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['ensino_medio'] = "O valor do objeto ensino_medio deve ser S ou N";
        }
        if (isset($arPost['ensino_fundamental']) && !in_array($arPost['ensino_fundamental'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['ensino_fundamental'] = "O valor do objeto ensino_fundamental deve ser S ou N";
        }
        if (isset($arPost['ensino_pre_escola']) && !in_array($arPost['ensino_pre_escola'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['ensino_pre_escola'] = "O valor do objeto ensino_pre_escola deve ser S ou N";
        }
        if (isset($arPost['mec_tp_dependencia']) && !in_array($arPost['mec_tp_dependencia'], \Db\Enum\MecTpDependencia::DEPENDENCIA)) {
            $boValidate = false;
            $arErros['mec_tp_dependencia'] = "O valor do objeto mec_tp_dependencia está inválido. Verifique e tente novamente!";
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

    public function removerRegistroById($codigoCidade, $idEscola) {
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

    public function associarVariosAlunos($codigoCidade, $idEscola, $arAlunos) {
        $dbSetePGEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        $arIdsAlunos = [];
        foreach ($arAlunos as $idAluno) {
            if ($idAluno['id_aluno'] !== "") {
                $arIdsAlunos[] = $idAluno['id_aluno'];
            }
        }
        $arRetorno = [];

        foreach ($arIdsAlunos as $aluno) {
            $alunoAssociadoEscola = $dbSetePGEscolaTemAluno->alunoAssociadoEscola($aluno, $codigoCidade);
            if ($alunoAssociadoEscola) {
                $arRetorno[] = ['id_aluno' => $aluno, 'result' => false, 'messages' => "Aluno já associado a alguma escola. Verifique e tente novamente!"];
            } else {
                $arResult = $dbSetePGEscolaTemAluno->_inserir([
                    'id_escola' => $idEscola,
                    'id_aluno' => $aluno,
                    'codigo_cidade' => $codigoCidade
                ]);
                $arRetorno[] = ['id_aluno' => $aluno, 'result' => $arResult['result'], 'messages' => $arResult['messages']];
            }
        }

        return $arRetorno;
    }

    public function excluirVariasAssociacoesAlunos($codigoCidade, $idEscola, $arAlunos) {


        $dbSetePGEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        $arIdsAlunos = [];
        foreach ($arAlunos as $idAluno) {
            if ($idAluno->id_aluno !== "") {
                $arIdsAlunos[] = $idAluno->id_aluno;
            }
        }
        $arRetorno = [];

        foreach ($arIdsAlunos as $aluno) {
            $arIds['codigo_cidade'] = $codigoCidade;
            $arIds['id_escola'] = $idEscola;
            $arIds['id_aluno'] = $aluno;
            $arResult = $dbSetePGEscolaTemAluno->_delete($arIds);
            $arRetorno[] = ['id_aluno' => $aluno, 'result' => $arResult['result'], 'messages' => $arResult['messages']];
        }

        return $arRetorno;
    }

}
