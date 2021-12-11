<?php

namespace Sete\V1\Rest\Censo;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CensoModel {

    protected $_entityEscolas;
    protected $_entityAlunos;
    protected $_entityEscolaTemAlunos;

    public function __construct() {
        $this->_entityEscolas = new \Db\SetePG\SeteEscolas();
        $this->_entityAlunos = new \Db\SetePG\SeteAlunos();
        $this->_entityEscolaTemAlunos = new \Db\SetePG\SeteEscolaTemAluno();
    }

    public function validarEscolas($codigoCidade, $arEscolas) {
        $boValido = true;
        $arErros = [];
        $arValoresBooleanos = ['S', 'N'];

        foreach ($arEscolas as $key => $rowEscola) {
            if (!isset($rowEscola['nome']) || empty($rowEscola['nome'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo nome da escola está ausente!";
            }
            if (!isset($rowEscola['mec_co_entidade']) || empty($rowEscola['mec_co_entidade'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_co_entidade da escola está ausente!";
            }
            if (!isset($rowEscola['mec_co_uf']) || empty($rowEscola['mec_co_uf'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_co_uf da escola está ausente!";
            }
            if (!isset($rowEscola['mec_co_municipio']) || empty($rowEscola['mec_co_municipio'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_co_municipio da escola está ausente!";
            } else if ($codigoCidade != $rowEscola['mec_co_municipio']) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com código do municipio diferente da cidade informada.";
            }
            if (!isset($rowEscola['mec_no_entidade']) || empty($rowEscola['mec_no_entidade'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_no_entidade da escola está ausente!";
            }
            if (!isset($rowEscola['mec_tp_dependencia']) || empty($rowEscola['mec_tp_dependencia'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_tp_dependencia da escola está ausente!";
            } else if (!in_array($rowEscola['mec_tp_dependencia'], \Db\Enum\MecTpDependencia::DEPENDENCIA)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_tp_dependencia da escola está inválido!";
            }
            if (!isset($rowEscola['mec_tp_localizacao']) || empty($rowEscola['mec_tp_localizacao'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_tp_localizacao da escola está ausente!";
            } else if (!in_array($rowEscola['mec_tp_localizacao'], \Db\Enum\MecTpLocalizacao::LOCALIZACAO)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_tp_localizacao da escola está inválido!";
            }
            if (!isset($rowEscola['mec_in_regular']) || empty($rowEscola['mec_in_regular'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_regular da escola está ausente!";
            } else if (!in_array($rowEscola['mec_in_regular'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_regular da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['mec_in_eja']) || empty($rowEscola['mec_in_eja'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_eja da escola está ausente!";
            } else if (!in_array($rowEscola['mec_in_eja'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_eja da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['mec_in_profissionalizante']) || empty($rowEscola['mec_in_profissionalizante'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_profissionalizante da escola está ausente!";
            } else if (!in_array($rowEscola['mec_in_profissionalizante'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_profissionalizante da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['mec_in_especial_exclusiva']) || empty($rowEscola['mec_in_especial_exclusiva'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_especial_exclusiva da escola está ausente!";
            } else if (!in_array($rowEscola['mec_in_especial_exclusiva'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_especial_exclusiva da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['mec_in_especial_exclusiva']) || empty($rowEscola['mec_in_especial_exclusiva'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_especial_exclusiva da escola está ausente!";
            } else if (!in_array($rowEscola['mec_in_especial_exclusiva'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_in_especial_exclusiva da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['horario_matutino']) || empty($rowEscola['horario_matutino'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo horario_matutino da escola está ausente!";
            } else if (!in_array($rowEscola['horario_matutino'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo horario_matutino da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['horario_vespertino']) || empty($rowEscola['horario_vespertino'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo horario_vespertino da escola está ausente!";
            } else if (!in_array($rowEscola['horario_vespertino'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo horario_vespertino da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['horario_noturno']) || empty($rowEscola['horario_noturno'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo horario_noturno da escola está ausente!";
            } else if (!in_array($rowEscola['horario_noturno'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo horario_noturno da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['ensino_superior']) || empty($rowEscola['ensino_superior'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_superior da escola está ausente!";
            } else if (!in_array($rowEscola['ensino_superior'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_superior da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['ensino_medio']) || empty($rowEscola['ensino_medio'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_medio da escola está ausente!";
            } else if (!in_array($rowEscola['ensino_medio'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_medio da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['ensino_fundamental']) || empty($rowEscola['ensino_fundamental'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_fundamental da escola está ausente!";
            } else if (!in_array($rowEscola['ensino_fundamental'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_fundamental da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['ensino_pre_escola']) || empty($rowEscola['ensino_pre_escola'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_pre_escola da escola está ausente!";
            } else if (!in_array($rowEscola['ensino_pre_escola'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_pre_escola da escola está inválido! São aceito S ou N como valores.";
            }
            if (!isset($rowEscola['contato_responsavel']) || empty($rowEscola['contato_responsavel'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo contato_responsavel da escola está ausente!";
            }
        }

        return ['result' => $boValido, 'messages' => $arErros];
    }

    public function validarAlunos($arAlunos) {
        $boValido = true;
        $arErros = [];
        $arValoresBooleanos = ['S', 'N'];

        foreach ($arAlunos as $key => $rowAluno) {
            if (!isset($rowAluno['nome']) || empty($rowAluno['nome'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo nome do aluno está ausente!";
            }
            if (!isset($rowAluno['data_nascimento']) || empty($rowAluno['data_nascimento'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo data_nascimento do aluno está ausente!";
            }
            if (!isset($rowAluno['sexo']) || empty($rowAluno['sexo'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo sexo do aluno está ausente!";
            } else if (!in_array($rowAluno['sexo'], \Db\Enum\Sexo::SEXOS)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo ensino_pre_escola da escola está inválido!";
            }


            if (!isset($rowAluno['cor']) || $rowAluno['cor'] === "") {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo cor do aluno está ausente!";
            } else if (!in_array($rowAluno['cor'], \Db\Enum\CorRaca::COR_RACA)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo cor da escola está inválido!";
            }
            if (!isset($rowAluno['turno']) || empty($rowAluno['turno'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo turno do aluno está ausente!";
            } else if (!in_array($rowAluno['turno'], \Db\Enum\Turno::TURNO)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo turno da escola está inválido!";
            }
            if (!isset($rowAluno['nivel']) || empty($rowAluno['nivel'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo nivel do aluno está ausente!";
            } else if (!in_array($rowAluno['nivel'], \Db\Enum\NivelAluno::NIVEL)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo nivel da escola está inválido!";
            }
            if (!isset($rowAluno['mec_tp_localizacao']) || empty($rowAluno['mec_tp_localizacao'])) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_tp_localizacao do aluno está ausente!";
            } else if (!in_array($rowAluno['mec_tp_localizacao'], \Db\Enum\MecTpLocalizacao::LOCALIZACAO)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo mec_tp_localizacao da escola está inválido!";
            }
            if (isset($rowAluno['cpf']) && !empty($rowAluno['cpf'])) {
                $cpfValido = \Application\Utils\Utils::validarCpf($rowAluno['cpf']);
                if (!$cpfValido) {
                    $boValidate = false;
                    $arErros[$key][] = "Registro na Posição {$key} está com o cpf inválido!";
                }
            }
            if (isset($rowAluno['def_caminhar']) && !empty($rowAluno['def_caminhar']) && !in_array($rowAluno['def_caminhar'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo def_caminhar do aluno está inválido! São aceito S ou N como valores.";
            }
            if (isset($rowAluno['def_ouvir']) && !empty($rowAluno['def_ouvir']) && !in_array($rowAluno['def_ouvir'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo def_ouvir do aluno está inválido! São aceito S ou N como valores.";
            }
            if (isset($rowAluno['def_enxergar']) && !empty($rowAluno['def_enxergar']) && !in_array($rowAluno['def_enxergar'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo def_enxergar do aluno está inválido! São aceito S ou N como valores.";
            }
            if (isset($rowAluno['def_mental']) && !empty($rowAluno['def_mental']) && !in_array($rowAluno['def_mental'], $arValoresBooleanos)) {
                $boValido = false;
                $arErros[$key][] = "Registro na Posição {$key} com campo def_mental do aluno está inválido! São aceito S ou N como valores.";
            }
        }

        return ['result' => $boValido, 'messages' => $arErros];
    }

    public function processarImportacaoEscola($arEscolas, $usuarioAutenticado, $codigoCidade) {
        $dbSeteEscolas = new \Db\SetePG\SeteEscolas();
        $arOperacaoResult = [];
        $arMessages = [];
        $boOperacao = true;
        foreach ($arEscolas as $key => $rowEscola) {
            $arIds['codigo_cidade'] = $codigoCidade;
            $arIds['mec_co_entidade'] = $rowEscola['mec_co_entidade'];
            $escolaExisteNoBD = $dbSeteEscolas->escolaExisteByCodigoMEC($arIds['codigo_cidade'], $arIds['mec_co_entidade']);
            unset($rowEscola['id_escola']);
            if ($escolaExisteNoBD) {
                $arEscolaBD = $dbSeteEscolas->getByCodEntidadeMec($arIds);
                $arIds['id_escola'] = $arEscolaBD['id_escola'];
                $rowEscola['alterado_por'] = $usuarioAutenticado;
                $rowEscola['dt_alteracao'] = date("Y-m-d H:i:s");
                $arOperacaoResult[$key] = $dbSeteEscolas->_atualizar($arIds, $rowEscola);
            } else {
                $rowEscola['codigo_cidade'] = $codigoCidade;
                $rowEscola['mec_in_regular'] = isset($rowEscola['mec_in_regular']) ? $rowEscola['mec_in_regular'] : 'N';
                $rowEscola['mec_in_eja'] = isset($rowEscola['mec_in_eja']) ? $rowEscola['mec_in_eja'] : 'N';
                $rowEscola['mec_in_profissionalizante'] = isset($rowEscola['mec_in_profissionalizante']) ? $rowEscola['mec_in_profissionalizante'] : 'N';
                $rowEscola['mec_in_especial_exclusiva'] = isset($rowEscola['mec_in_especial_exclusiva']) ? $rowEscola['mec_in_especial_exclusiva'] : 'N';
                $rowEscola['horario_matutino'] = isset($rowEscola['horario_matutino']) ? $rowEscola['horario_matutino'] : 'N';
                $rowEscola['horario_vespertino'] = isset($rowEscola['horario_vespertino']) ? $rowEscola['horario_vespertino'] : 'N';
                $rowEscola['horario_noturno'] = isset($rowEscola['horario_noturno']) ? $rowEscola['horario_noturno'] : 'N';
                $rowEscola['ensino_superior'] = isset($rowEscola['ensino_superior']) ? $rowEscola['ensino_superior'] : 'N';
                $rowEscola['ensino_medio'] = isset($rowEscola['ensino_medio']) ? $rowEscola['ensino_medio'] : 'N';
                $rowEscola['ensino_fundamental'] = isset($rowEscola['ensino_fundamental']) ? $rowEscola['ensino_fundamental'] : 'N';
                $rowEscola['ensino_pre_escola'] = isset($rowEscola['ensino_pre_escola']) ? $rowEscola['ensino_pre_escola'] : 'N';
                $rowEscola['criado_por'] = $usuarioAutenticado;
                $rowEscola['dt_criacao'] = date("Y-m-d H:i:s");
                $arOperacaoResult[$key] = $dbSeteEscolas->_inserir($rowEscola);
            }
        }

        foreach ($arOperacaoResult as $rowOP) {
            if (!$rowOP['result']) {
                $boOperacao = false;
                $arMessages[] = $rowOP['messages'];
            }
        }
        return ['result' => $boOperacao, 'messages' => $arMessages];
    }

    public function processarImportacaoAluno($arAlunos, $usuarioAutenticado, $codigoCidade) {
        $arOperacaoResult = [];
        $arDadosAlunos = [];
        $arMessages = [];
        $boOperacao = true;
        foreach ($arAlunos as $key => $rowAluno) {
            if (isset($rowAluno['cpf']) && !empty($rowAluno['cpf'])) {
                $arDadosAlunos[$key] = $this->checarAlunoComCPF($rowAluno, $usuarioAutenticado, $codigoCidade);
            } else {
                $arDadosAlunos[$key] = $this->checarAlunoSemCPF($rowAluno, $usuarioAutenticado, $codigoCidade);
            }
        }

        foreach ($arDadosAlunos as $key => $row) {
            $row['alunoRow']['codigo_cidade'] = $codigoCidade;
            $codigoMecEscola = $row['alunoRow']['id_escola'];
            $idEscola = $this->_entityEscolas->getIdEscolaByCodigoMecAndCodigoCidade($codigoMecEscola, $codigoCidade);
            if (sizeof($row['alunoBD']) > 2) {
                $arIdsDeletarRelacaoEscolaAluno['codigo_cidade'] = $row['alunoRow']['codigo_cidade'];
                $arIdsDeletarRelacaoEscolaAluno['id_aluno'] = $row['alunoBD']['id_aluno'];
                $this->_entityEscolaTemAlunos->_deleteAssociacaoAluno($arIdsDeletarRelacaoEscolaAluno);
                $row['alunoRow']['dt_alteracao'] = date("Y-m-d H:i:s");
                $row['alunoRow']['alterado_por'] = $usuarioAutenticado;

                $arId['codigo_cidade'] = $row['alunoBD']['codigo_cidade'];
                $arId['id_aluno'] = $row['alunoBD']['id_aluno'];
                /* $idsAlunosJaExiste = $this->_entityAlunos->alunoExisteById($arId);
                  //se os ids já existir na base de dados, o sistema exclui o registro e insere um novo na nova cidade
                  if($idsAlunosJaExiste){
                  $this->_entityAlunos->_delete($arId);
                  $this->_entityAlunos->_inserir($row['alunoRow']);
                  $idAluno = $this->_entityAlunos->getUltimoIdInserido();
                  }else{ */
                $row['alunoRow']['id_escola'] = $idEscola;
                $idAluno = $row['alunoBD']['id_aluno'];
                $arOperacaoResult[$key] = $this->_entityAlunos->_atualizar($arId, $row['alunoRow']);
                //}
                $arDadosEscolaTemAluno = [
                    'id_aluno' => $idAluno,
                    'id_escola' => $idEscola,
                    'codigo_cidade' => $codigoCidade
                ];
                var_dump($arDadosEscolaTemAluno);
                exit;
                $arOPESTA = $this->_entityEscolaTemAlunos->_inserir($arDadosEscolaTemAluno);
                //echo "Atualização <br />";
                // var_dump($arOPESTA);
                //echo "Atualizar " . $row['alunoBD']['nome'] . "<br />";
            } else {
                $row['alunoRow']['da_porteira'] = isset($row['alunoRow']['da_porteira']) ? $row['alunoRow']['da_porteira'] : 'N';
                $row['alunoRow']['da_mataburro'] = isset($row['alunoRow']['da_mataburro']) ? $row['alunoRow']['da_mataburro'] : 'N';
                $row['alunoRow']['da_colchete'] = isset($row['alunoRow']['da_colchete']) ? $row['alunoRow']['da_colchete'] : 'N';
                $row['alunoRow']['da_atoleiro'] = isset($row['alunoRow']['da_atoleiro']) ? $row['alunoRow']['da_atoleiro'] : 'N';
                $row['alunoRow']['da_ponterustica'] = isset($row['alunoRow']['da_ponterustica']) ? $row['alunoRow']['da_ponterustica'] : 'N';
                $row['alunoRow']['dt_criacao'] = date("Y-m-d H:i:s");
                $row['alunoRow']['criado_por'] = $usuarioAutenticado;
                $arOperacaoResult[$key] = $this->_entityAlunos->_inserir($row['alunoRow']);
                $idNovoAluno = $this->_entityAlunos->getUltimoIdInserido();
                if ($arOperacaoResult[$key]['result']) {
                    $arOperacaoResult[$key]['messages']['id'] = $this->_entityAlunos->getUltimoIdInserido();
                    $arDadosEscolaTemAluno = [
                        'id_aluno' => $idNovoAluno,
                        'id_escola' => $idEscola,
                        'codigo_cidade' => $codigoCidade
                    ];
                    var_dump($arDadosEscolaTemAluno);
                    exit;
                    $arOPESTA = $this->_entityEscolaTemAlunos->_inserir($arDadosEscolaTemAluno);
                    // echo "Inserção <br />";
                    // var_dump($arOPESTA);
                }

                //echo "Inserir " . $row['alunoBD']['nome'] . "<br />";
            }
        }
        foreach ($arOperacaoResult as $rowOP) {
            if (!$rowOP['result']) {
                $boOperacao = false;
                $arMessages[] = $rowOP['messages'];
            }
        }
        return ['result' => $boOperacao, 'messages' => $arMessages];
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
