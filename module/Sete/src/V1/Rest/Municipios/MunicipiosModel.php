<?php
namespace Sete\V1\Rest\Municipios;

class MunicipiosModel {
    
    protected $_entity;
    
    public function __construct() {
        $this->_entity = new MunicipiosEntity();
    }
    
    public function getAll(){
        $arDados = $this->_entity->getLista();
        return $arDados;
    }
    
    public function getById($codigo){
        return $this->_entity->getByCodigoIBGE($codigo);
    }
    
}

