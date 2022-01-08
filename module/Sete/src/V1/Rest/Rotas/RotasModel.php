<?php

namespace Sete\V1\Rest\Rotas;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RotasModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteRotas();
    }

    public function getAll($codigoMunicipio) {
        $urlHelper = new \Application\Utils\UrlHelper();
        $arDados = $this->_entity->getLista($codigoMunicipio);
        foreach ($arDados as $key => $row){
            $arDados[$key]['gps'] = (!empty($row['shape']) ? 'Sim' : 'Não');
            unset($arDados[$key]['shape']);
            $arDados[$key]['_links']['_self'] = $urlHelper->baseUrl("rotas/{$codigoMunicipio}/{$row['id_rota']}");
        }
        return $arDados;
    }

    public function getById($codigoCidade, $idRota) {
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_rota'] = $idRota;
        $arRow = $this->_entity->getById($arIds);
        unset($arRow['shape']);
        $urlHelper = new \Application\Utils\UrlHelper();
        $arRow['_links']['_self'] = $urlHelper->baseUrl("rotas/{$codigoCidade}/{$idRota}/veiculos");
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
            $arErros['nome'] = "O nome da rota deve ser informado!";
        }
        if (!isset($arPost['km']) || empty($arPost['km'])) {
            $boValidate = false;
            $arErros['km'] = "Informe a distância da rota para continuar!";
        }
        if (!isset($arPost['tempo']) || empty($arPost['tempo'])) {
            $boValidate = false;
            $arErros['tempo'] = "Informe a duração em minutos da rota para continuar!";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertRota($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    private function validarParametrosInsertRota($arPost) {
        $boValidate = true;
        $arErros = [];
        $arValoresBooleanos = ['S', 'N'];
        if (isset($arPost['da_porteira']) && !in_array($arPost['da_porteira'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_porteira'] = "O o valor do objeto da_porteira deve ser S ou N";
        }
        if (isset($arPost['da_mataburro']) && !in_array($arPost['da_mataburro'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_mataburro'] = "O valor do objeto da_mataburro deve ser S ou N";
        }
        if (isset($arPost['da_colchete']) && !in_array($arPost['da_colchete'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_colchete'] = "O valor do objeto da_colchete deve ser S ou N";
        }
        if (isset($arPost['da_atoleiro']) && !in_array($arPost['da_atoleiro'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_atoleiro'] = "O o valor do objeto da_atoleiro deve ser S ou N";
        }
        if (isset($arPost['da_ponterustica']) && !in_array($arPost['da_ponterustica'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['da_ponterustica'] = "O o valor do objeto da_ponterustica deve ser S ou N";
        }
        if (isset($arPost['turno_matutino']) && !in_array($arPost['turno_matutino'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['turno_matutino'] = "O o valor do objeto turno_matutino deve ser S ou N";
        }
        if (isset($arPost['turno_vespertino']) && !in_array($arPost['turno_vespertino'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['turno_vespertino'] = "O o valor do objeto turno_vespertino deve ser S ou N";
        }
        if (isset($arPost['turno_noturno']) && !in_array($arPost['turno_noturno'], $arValoresBooleanos)) {
            $boValidate = false;
            $arErros['turno_noturno'] = "O o valor do objeto turno_noturno deve ser S ou N";
        }
        if (isset($arPost['tipo']) && !in_array($arPost['tipo'], \Db\Enum\Rota\Tipo::TIPO)) {
            $boValidate = false;
            $arErros['tipo'] = "O valor do objeto tipo está inválido. Verifique e tente novamente!";
        }
        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function validarUpdate($arPost, $idRota) {
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
            $arErros['nome'] = "O nome da rota deve ser informado!";
        }
        if (!isset($arPost['km']) || empty($arPost['km'])) {
            $boValidate = false;
            $arErros['km'] = "Informe a distância da rota para continuar!";
        }
        if (!isset($arPost['tempo']) || empty($arPost['tempo'])) {
            $boValidate = false;
            $arErros['tempo'] = "Informe a duração em minutos da rota para continuar!";
        }
        if ($boValidate) {
            return $this->validarParametrosInsertRota($arPost);
        } else {
            return ['result' => $boValidate, 'messages' => $arErros];
        }
    }

    public function prepareUpdate($idRota, $arPost) {
        $arPost = (Array) $arPost;
        $codigoCidade = $arPost['codigo_cidade'];
        unset($arPost['codigo_cidade']);
        unset($arPost['id_aluno']);
        $arPost['da_porteira'] = isset($arPost['da_porteira']) ? $arPost['da_porteira'] : 'N';
        $arPost['da_mataburro'] = isset($arPost['da_mataburro']) ? $arPost['da_mataburro'] : 'N';
        $arPost['da_colchete'] = isset($arPost['da_colchete']) ? $arPost['da_colchete'] : 'N';
        $arPost['da_atoleiro'] = isset($arPost['da_atoleiro']) ? $arPost['da_atoleiro'] : 'N';
        $arPost['da_ponterustica'] = isset($arPost['da_ponterustica']) ? $arPost['da_ponterustica'] : 'N';
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['id_rota'] = $idRota;
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
