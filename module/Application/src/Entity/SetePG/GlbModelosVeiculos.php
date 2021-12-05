<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class GlbModelosVeiculos extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'glb_modelos_veiculos';
        $this->primaryKey = 'id_modelo';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getLista(){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['marc' => $this->tableIdentifier])
                ->columns(['id' => 'id_modelo', 'modelo' => 'nome_modelo'])
                ->order("marc.nome_modelo ASC");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function getNomeById($id){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['marc' => $this->tableIdentifier])
            ->columns(['marca' => 'nm_marca'])
            ->where("marc.id_marca = {$id}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['marca'];        
    }
    

}
