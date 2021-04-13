<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteRotas extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_rotas';
        $this->primaryKey = 'id_rota';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function getIdRotaByIdFirebase($municipio, $codigoFirebase){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_rota'])
                ->where("id_firebase = '{$codigoFirebase}'")
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute();
        if($row->count() > 0){
            $row = $row->current();
            return $row['id_rota'];
        }else{
            return false;
        }
    }
    
    public function qtdRotas($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }
    
    public function qtdRotasKilometragemTotal($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("sum(km)")])
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    } 
    
    public function qtdRotasKilometragemMedia($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("avg(km)")])
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    } 
    
    public function qtdRotasTempoMedio($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("avg(tempo)")])
                ->where("codigo_cidade = {$municipio}");
                echo $sql->prepareStatementForSqlObject($select);
                exit;
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    } 
    
    
    

}
