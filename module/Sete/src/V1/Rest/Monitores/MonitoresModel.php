<?php

namespace Sete\V1\Rest\Monitores;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MonitoresModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteMonitores();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row) {
            if (!empty($row['data_nascimento'])) {
                $row['data_nascimento'] = date("d/m/Y", strtotime($row['data_nascimento']));
            }
            $arDados[$key]['_links']['_self'] = $urlHelper->baseUrl("monitores/{$codigoMunicipio}/{$row['cpf']}");
        }
        return $arDados;
    }

    public function getById($codigoCidade, $cpfMonitor) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['cpf_monitor'] = $cpfMonitor;
        $arRow = $this->_entity->getById($arIds);
        if (!empty($arRow)) {
            $arRow['data_nascimento'] = date("d/m/Y", strtotime($arRow['data_nascimento']));
        }
        //$urlHelper = new \Application\Utils\UrlHelper();
        //$arRow['_links']['_self'] = $urlHelper->baseUrl("motoristas/{$codigoCidade}/{$cpfMotorista}/escola");
        return $arRow;
    }

    public function prepareInsert($arPost, $accessToken) {
        $dbAccessToken = new \Db\Core\AccessToken();
        $arPost = (Array) $arPost;
        $arPost['turno_manha'] = isset($arPost['turno_manha']) ? $arPost['turno_manha'] : 'N';
        $arPost['turno_tarde'] = isset($arPost['turno_tarde']) ? $arPost['turno_tarde'] : 'N';
        $arPost['turno_noite'] = isset($arPost['turno_noite']) ? $arPost['turno_noite'] : 'N';
        $arPost['dt_criacao'] = date("Y-m-d H:i:s");
        $arPost['criado_por'] = $dbAccessToken->getEmailUsuarioSETEByAccessToken($accessToken);
        $arResult = $this->_entity->_inserir($arPost);
        if ($arResult['result']) {
            unset($arResult['messages']['id']);
            $arResult['messages']['cpf'] = $arPost['cpf'];
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
            $arErros['nome'] = "O nome do monitor deve ser informado!";
        }
        if (!isset($arPost['sexo']) || empty($arPost['sexo'])) {
            $boValidate = false;
            $arErros['sexo'] = "O sexo do monitor deve ser informado!";
        }
        if (isset($arPost['cpf']) && !empty($arPost['cpf'])) {
            $cpfValido = \Application\Utils\Utils::validarCpf($arPost['cpf']);
            $dbMonitor = new \Db\SetePG\SeteMonitores();
            if (!$cpfValido) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado é inválido!";
            }
            if ($dbMonitor->monitorExiste($arPost['cpf'])) {
                $boValidate = false;
                $arErros['cpf'] = "O cpf informado já existe!";
            }
        } else {
            $boValidate = false;
            $arErros['cpf'] = "O cpf é obrigatório!";
        }
        if (!isset($arPost['data_nascimento']) || empty($arPost['data_nascimento'])) {
            $boValidate = false;
            $arErros['data_nascimento'] = "O campo data de nascimento deve ser informado!";
        } else {
            if (!\Application\Utils\Utils::ValidaDataDDMMYYYY($arPost['data_nascimento'])) {
                $boValidate = false;
                $arErros['data_nascimento'] = "A data de nascimento informada é inválida!";
            }
        }
        if (!isset($arPost['vinculo']) || empty($arPost['vinculo'])) {
            $boValidate = false;
            $arErros['vinculo'] = "O vínculo do monitor com a administração pública deve ser informado!";
        }
        /* if (!isset($arPost['data_validade_cnh']) || empty($arPost['data_validade_cnh'])) {
          $boValidate = false;
          $arErros['data_validade_cnh'] = "O campo data de validade da CNH deve ser informado!";
          } else {
          if (!\Application\Utils\Utils::ValidaDataDDMMYYYY($arPost['data_validade_cnh'])) {
          $boValidate = false;
          $arErros['data_validade_cnh'] = "A data de validade da CNH informada é inválida!";
          }
          } */
        if (!isset($arPost['turno_manha']) && !isset($arPost['turno_tarde']) && !isset($arPost['turno_noite'])) {
            $boValidate = false;
            $arErros['turno'] = "Informe ao menos um turno de trabalho para o monitor.";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertMonitor($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    private function validarParametrosInsertMonitor($arPost) {
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
        if (isset($arPost['sexo']) && !in_array($arPost['sexo'], \Db\Enum\Sexo::SEXOS)) {
            $boValidate = false;
            $arErros['sexo'] = "O valor do objeto sexo está inválido. Verifique e tente novamente!";
        }
        if (isset($arPost['vinculo']) && !in_array($arPost['vinculo'], \Db\Enum\VinculoServidor::VINCULOS)) {
            $boValidate = false;
            $arErros['vinculo'] = "O valor do objeto vinculo está inválido. Verifique e tente novamente!";
        }
        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function validarUpdate($arPost, $idAluno) {
        $arPost = (Array) $arPost;
        $boValidate = true;
        $arErros = [];
        if (isset($arPost['nome']) && empty($arPost['nome'])) {
            $boValidate = false;
            $arErros['nome'] = "O nome do monitor deve ser informado!";
        }
        if (isset($arPost['data_nascimento']) && empty($arPost['data_nascimento'])) {
            $boValidate = false;
            $arErros['data_nascimento'] = "O campo data de nascimento deve ser informada!";
        } else if (!empty($arPost['data_nascimento']) && !\Application\Utils\Utils::ValidaDataDDMMYYYY($arPost['data_nascimento'])) {
            $boValidate = false;
            $arErros['data_nascimento'] = "A data informada é inválida!";
        }
        /* if (!isset($arPost['data_validade_cnh']) || empty($arPost['data_validade_cnh'])) {
          $boValidate = false;
          $arErros['data_validade_cnh'] = "O campo data de validade da CNH deve ser informado!";
          } else {
          if (!\Application\Utils\Utils::ValidaDataDDMMYYYY($arPost['data_validade_cnh'])) {
          $boValidate = false;
          $arErros['data_validade_cnh'] = "A data de validade da CNH informada é inválida!";
          }
          } */
        if ($boValidate) {
            return $this->validarParametrosInsertMonitor($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    public function prepareUpdate($codigoCidade, $cpfMonitor, $arPost) {
        $arPost = (Array) $arPost;
        //Destrói as variaveis codigo cidade e cpf para não atualizar no banco de dados
        unset($arPost['codigo_cidade']);
        unset($arPost['cpf']);
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['cpf'] = $cpfMonitor;
        $arResult = $this->_entity->_atualizar($arId, $arPost);
        return $arResult;
    }

    public function removerRegistroById($codigoCidade, $cpfMonitor) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['cpf'] = $cpfMonitor;
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
