<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class GlbMunicipios extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'glb_municipio';
        $this->primaryKey = 'id_cidade';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function municipioExiste($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_ibge = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if($row['qtd'] > 0){
            return true;
        }else{
            return false;
        }
    }    
    
    

}
