<?php

namespace Sete\V1\Rest\Acesso;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AcessoModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\Sistema\RecuperarSenha();
    }

    public function getAll($codigoMunicipio) {
        return [];
    }

    public function getById($codigoCidade, $idAluno) {
        return [];
    }

    public function prepareInsert($arPost) {
        return [];
    }

    public function validarInsert($arPost) {
        return [];
    }

    public function validarUpdate($arPost) {
        $boValidate = true;
        $arErros = [];
        if (empty($arPost->email) || !\Application\Utils\Utils::validarEmail($arPost->email)) {
            $boValidate = false;
            $arErros[] = "O parâmetro email deve ser válido!";
        }
        if (empty($arPost->key)) {
            $boValidate = false;
            $arErros[] = "Informe o código de recuperação para continuar!";
        } else {
            $dbSeteRecuperarSenha = new \Db\Sistema\RecuperarSenha();
            if (!$dbSeteRecuperarSenha->tokenIsValido($arPost->key)) {
                $boValidate = false;
                $arErros[] = "O código de recuperação informado é inválido ou já foi utilizado. Verifique e tente novamente!";
            }
        }
        if (empty($arPost->senha) || !$this->isValidMd5($arPost->senha)) {
            $boValidate = false;
            $arErros[] = "O parâmetro senha deve ser um hash md5!";
        }
        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function isValidMd5($md5 = '') {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }

    public function prepareUpdate($codigoCidade, $arPost) {
        $arPost = (Array) $arPost;
        $dbSeteToken = new \Db\Sistema\RecuperarSenha();
        $arToken = $dbSeteToken->getDadosToken($arPost['key']);
        $arId['codigo_cidade'] = $codigoCidade;
        $arId['id_usuario'] = $arToken['id_usuario'];
        $dbSeteUsuarios = new \Db\SetePG\SeteUsuarios();
        $this->_entity->_atualizar($arToken['id_recuperacao'], ['is_usado' => 'S']);
        $arResult = $dbSeteUsuarios->_atualizar($arId, ['password' => $arPost['senha'], 'dt_alteracao' => date("Y-m-d H:i:s")]);
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
