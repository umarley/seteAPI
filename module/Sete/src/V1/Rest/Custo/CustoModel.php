<?php

namespace Sete\V1\Rest\Custo;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CustoModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteAlunos();
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

    private function validarParametrosInsertAluno($arPost) {
        return [];
    }

    public function validarUpdate($arPost, $idAluno) {
        return [];
    }

    public function prepareUpdate($codigoCidade, $idAluno, $arPost) {
        return [];
    }
    
    public function checarRotaAndCidadeExistem($codigoCidade, $idRota){
        $dbGlbMunicipio = new \Db\SetePG\GlbMunicipios();
        $dbSeteRotas    = new \Db\SetePG\SeteRotas();
        $boValidate['result'] = true;
        if(!$dbGlbMunicipio->municipioExiste($codigoCidade)){
            $boValidate['result'] = false;
            $boValidate['messages'] = "Código do municipio informado não existe!";
            $boValidate['http_code'] = 404;
        }else if(!$dbSeteRotas->rotaExiste($idRota, $codigoCidade)){
            $boValidate['result'] = false;
            $boValidate['messages'] = "O Id da Rota informado não existe!";
            $boValidate['http_code'] = 404;
        }
        return $boValidate;
    }
    
    public function validarParametrosCusto($codigoCidade, $idRota){
        $dbSeteParametros = new \Db\SetePG\SeteParametros();
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $arParametrosGlobais = $dbSeteParametros->getParametros($codigoCidade);
        
        
        var_dump($arParametrosGlobais);
        
        
        exit;
    }
    
    private function validarDadosParametrosParaCalculoCusto($arParametros){
        if(empty($arParametros['PERC_ENCARGO_SOCIAIS'])){
            
        }
        
        
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
