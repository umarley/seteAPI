<?php

namespace Sete\V1\Rest\Localidades;


class LocalidadesModel {

    protected $_entityMunicipios;
    protected $_entityEstados;

    public function __construct() {
        $this->_entityMunicipios = new \Db\SetePG\GlbMunicipios();
        $this->_entityEstados    = new \Db\SetePG\GlbEstados(); 
    }

    public function getTodosEstados(){
        return $this->_entityEstados->getLista();
    }
    
    public function getTodosMunicipiosByEstado($codigoEstado){
        return $this->_entityMunicipios->getListaByEstado($codigoEstado);
    }

}
