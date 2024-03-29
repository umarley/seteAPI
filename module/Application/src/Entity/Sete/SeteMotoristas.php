<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteMotoristas extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_motoristas';
        $this->primaryKey = 'cpf';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function getCPFByIdFirebase($municipio, $idFirebase){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['cpf'])
                ->where("id_firebase = '{$idFirebase}'")
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['cpf'];
    }
    
    public function qtdMotoristas($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }  
    
    
    

}
