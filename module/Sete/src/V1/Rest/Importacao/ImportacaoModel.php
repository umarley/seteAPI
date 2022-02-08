<?php

namespace Sete\V1\Rest\Importacao;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportacaoModel {

    protected $_entityEscolas;
    protected $_entityAlunos;
    protected $_entityEscolaTemAlunos;

    public function __construct() {
        $this->_entityEscolas = new \Db\SetePG\SeteEscolas();
        $this->_entityAlunos = new \Db\SetePG\SeteAlunos();
        $this->_entityEscolaTemAlunos = new \Db\SetePG\SeteEscolaTemAluno();
    }

    public function processarDadosPlanilha($arParams, $arData, $accessToken) {
        $boValidate = true;
        switch ($arParams['cadastro']) {
            case 'aluno':
                $boValidate = $this->validarAlunos($arData);
                if ($boValidate['result']) {
                    return $this->processarImportacaoAluno($arData, $accessToken, $arParams['codigo_cidade']);
                } else {
                    return $boValidate;
                }
                break;
        }
    }

    public function validarAlunos($rowAluno) {
        $rowAluno = (array) $rowAluno;
        $boValido = true;
        $arErros = [];
        if (!isset($rowAluno['nome']) || empty($rowAluno['nome'])) {
            $boValido = false;
            $arErros[] = "O campo nome do aluno está ausente!";
        }
        if (!isset($rowAluno['data_nascimento']) || empty($rowAluno['data_nascimento'])) {
            $boValido = false;
            $arErros[] = "O campo data_nascimento do aluno está ausente!";
        }
        if (!isset($rowAluno['sexo']) || empty($rowAluno['sexo'])) {
            $boValido = false;
            $arErros[] = "O campo sexo do aluno está ausente!";
        } else if (!in_array($rowAluno['sexo'], \Db\Enum\Sexo::SEXOS)) {
            $boValido = false;
            $arErros[] = "O campo sexo da escola está inválido!";
        }
        if (!isset($rowAluno['cor']) || $rowAluno['cor'] === "") {
            $boValido = false;
            $arErros[] = "O campo cor do aluno está ausente!";
        } else if (!in_array($rowAluno['cor'], \Db\Enum\CorRaca::COR_RACA)) {
            $boValido = false;
            $arErros[] = "O campo cor do aluno está inválido!";
        }
        if (!isset($rowAluno['turno']) || empty($rowAluno['turno'])) {
            $boValido = false;
            $arErros[] = "O campo turno do aluno está ausente!";
        } else if (!in_array($rowAluno['turno'], \Db\Enum\Turno::TURNO)) {
            $boValido = false;
            $arErros[] = "O campo turno do aluno está inválido!";
        }
        if (!isset($rowAluno['nivel']) || empty($rowAluno['nivel'])) {
            $boValido = false;
            $arErros[] = "O campo nivel do aluno está ausente!";
        } else if (!in_array($rowAluno['nivel'], \Db\Enum\NivelAluno::NIVEL)) {
            $boValido = false;
            $arErros[] = "O campo nivel do aluno está inválido!";
        }
        if (!isset($rowAluno['mec_tp_localizacao']) || empty($rowAluno['mec_tp_localizacao'])) {
            $boValido = false;
            $arErros[] = "O campo mec_tp_localizacao do aluno está ausente!";
        } else if (!in_array($rowAluno['mec_tp_localizacao'], \Db\Enum\MecTpLocalizacao::LOCALIZACAO)) {
            $boValido = false;
            $arErros[] = "O campo mec_tp_localizacao do aluno está inválido!";
        }
        if (isset($rowAluno['cpf']) && !empty($rowAluno['cpf'])) {
            $cpfValido = \Application\Utils\Utils::validarCpf($rowAluno['cpf']);
            if (!$cpfValido) {
                $boValido = false;
                $arErros[] = "O CPF está inválido!";
            }
        }
        return ['result' => $boValido, 'messages' => $arErros];
    }

    public function processarImportacaoAluno($rowAluno, $accessToken, $codigoCidade) {
        $rowAluno = (array) $rowAluno;
        $dbAccessToken = new \Db\Core\AccessToken();
        $usuarioAutenticado = $dbAccessToken->getEmailUsuarioSETEByAccessToken($accessToken);
        $arOperacaoResult = [];
        $arDadosAlunos = [];
        $arMessages = [];
        $boOperacao = true;
        if (isset($rowAluno['cpf']) && !empty($rowAluno['cpf'])) {
            $arDadosAluno = $this->checarAlunoComCPF($rowAluno, $usuarioAutenticado, $codigoCidade);
        } else {
            $arDadosAluno = $this->checarAlunoSemCPF($rowAluno, $usuarioAutenticado, $codigoCidade);
        }

        $row = $arDadosAluno;

        $row['alunoRow']['codigo_cidade'] = $codigoCidade;
       // $codigoMecEscola = $row['alunoRow']['id_escola'];
        //$idEscola = $this->_entityEscolas->getIdEscolaByCodigoMecAndCodigoCidade($codigoMecEscola, $codigoCidade);
        if (sizeof($row['alunoBD']) > 2) {
            $arIdsDeletarRelacaoEscolaAluno['codigo_cidade'] = $row['alunoBD']['codigo_cidade'];
            $arIdsDeletarRelacaoEscolaAluno['id_aluno'] = $row['alunoBD']['id_aluno'];
            $this->_entityEscolaTemAlunos->_deleteAssociacaoAluno($arIdsDeletarRelacaoEscolaAluno);
            $row['alunoRow']['dt_alteracao'] = date("Y-m-d H:i:s");
            $row['alunoRow']['alterado_por'] = $usuarioAutenticado;
            $arId['codigo_cidade'] = $row['alunoBD']['codigo_cidade'];
            $arId['id_aluno'] = $row['alunoBD']['id_aluno'];
            $idAluno = $row['alunoBD']['id_aluno'];
            $arOperacaoResult = $this->_entityAlunos->_atualizar($arId, $row['alunoRow']);
        } else {
            $row['alunoRow']['da_porteira'] = isset($row['alunoRow']['da_porteira']) ? $row['alunoRow']['da_porteira'] : 'N';
            $row['alunoRow']['da_mataburro'] = isset($row['alunoRow']['da_mataburro']) ? $row['alunoRow']['da_mataburro'] : 'N';
            $row['alunoRow']['da_colchete'] = isset($row['alunoRow']['da_colchete']) ? $row['alunoRow']['da_colchete'] : 'N';
            $row['alunoRow']['da_atoleiro'] = isset($row['alunoRow']['da_atoleiro']) ? $row['alunoRow']['da_atoleiro'] : 'N';
            $row['alunoRow']['da_ponterustica'] = isset($row['alunoRow']['da_ponterustica']) ? $row['alunoRow']['da_ponterustica'] : 'N';
            $row['alunoRow']['dt_criacao'] = date("Y-m-d H:i:s");
            $row['alunoRow']['criado_por'] = $usuarioAutenticado;
            $arOperacaoResult = $this->_entityAlunos->_inserir($row['alunoRow']);
            //$idNovoAluno = $this->_entityAlunos->getUltimoIdInserido();
        }
        return $arOperacaoResult;
    }

    private function checarAlunoComCPF($arAluno) {
        $alunoExiste = $this->_entityAlunos->alunoExiste($arAluno['cpf']);
        if ($alunoExiste) {
            $arDadosAlunoBD = $this->_entityAlunos->getByCPF($arAluno['cpf']);
        } else {
            $arDadosAlunoBD = $this->checarAlunoSemCPF($arAluno);
        }
        return ['alunoRow' => $arAluno, 'alunoBD' => $arDadosAlunoBD];
    }

    private function checarAlunoSemCPF($arAluno) {
        $chaveComparacao = str_replace(" ", "-", trim($arAluno['nome'])) . "-" . $this->formataDataNascimentoSQL($arAluno['data_nascimento']);
        $alunoExiste = $this->_entityAlunos->alunoExistePorChaveComposta($chaveComparacao);
        if ($alunoExiste) {
            $arDadosAlunoBD = $this->_entityAlunos->getAlunoPorChave($chaveComparacao);
        } else {
            $arDadosAlunoBD = [];
        }
        return ['alunoRow' => $arAluno, 'alunoBD' => $arDadosAlunoBD];
    }

    private function formataDataNascimentoSQL($data) {
        $parts = explode("/", $data);
        return $parts[2] . "-" . $parts[1] . "-" . $parts[0];
    }

}
