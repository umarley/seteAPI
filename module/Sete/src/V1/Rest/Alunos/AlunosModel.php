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
        $arDados = $this->_entity->qtdAlunosAtendidos($codigoMunicipio);
        return $arDados;
    }

    public function getById($codigo) {
        return [];
    }

    public function prepareInsert($arPost) {
        
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
        if(!isset($arPost['nome']) || empty($arPost['nome'])){
            $boValidate = false;
            $arErros['nome'] = "O nome do aluno deve ser informado!";
        }
        if(!isset($arPost['data_nascimento']) || empty($arPost['data_nascimento'])){
            $boValidate = false;
            $arErros['data_nascimento'] = "O campo data de nascimento deve ser informado!";
        }
        if(!isset($arPost['nome_responsavel']) || empty($arPost['nome_responsavel'])){
            $boValidate = false;
            $arErros['nome_responsavel'] = "O nome do responsável pelo aluno deve ser informado!";
        }
        if(!isset($arPost['grau_responsavel']) || empty($arPost['grau_responsavel'])){
            $boValidate = false;
            $arErros['grau_responsavel'] = "Informe o grau de parentesco do responsável pelo aluno!";
        }
        if($boValidate){
            return $this->validarParametrosInsertAluno($arPost);
        }else{
            return ['result' => $boValidate, 'messages' => $arErros];
        }        
    }
    
    private function validarParametrosInsertAluno($arPost){
        $arValoresBooleanos = ['S', 'N'];
        $arValoresSexo = [1, 2, 3];
        $arValoresCor = [1, 2, 3];
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
