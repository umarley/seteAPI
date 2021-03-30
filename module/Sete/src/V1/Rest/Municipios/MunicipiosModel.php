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
        $dbSeteEscolas = new \Db\Sete\SeteEscolas();
        $dbSeteAlunos = new \Db\Sete\SeteAlunos();
        $dbSeteVeiculos = new \Db\Sete\SeteVeiculos();
        $dbSeteRotas = new \Db\Sete\SeteRotas();
        $arData = $this->_entity->getByCodigoIBGE($codigo);
        $arData['data'] = [
            'n_escolas' => $dbSeteEscolas->qtdEscolasAtendidas($codigo),
            'n_alunos' => $dbSeteAlunos->qtdAlunosAtendidos($codigo),
            'n_veiculos_funcionamento' => $dbSeteVeiculos->qtdVeiculosFuncionando($codigo),
            'n_veiculos_manutencao' => $dbSeteVeiculos->qtdVeiculosManutencao($codigo),
            'n_rotas' => $dbSeteRotas->qtdRotas($codigo),
            'n_rotas_kilometragem_total' => $dbSeteRotas->qtdRotasKilometragemTotal($codigo),
            'n_rotas_kilometragem_media' => $dbSeteRotas->qtdRotasKilometragemMedia($codigo)
        ];
        return $arData;
    }
    
}

