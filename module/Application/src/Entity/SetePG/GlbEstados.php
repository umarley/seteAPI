<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class GlbEstados extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'glb_estado';
        $this->primaryKey = 'id_cidade';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getLista(){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['est' => $this->tableIdentifier])
                ->columns(['codigo', 'nome', 'uf']);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    

}
