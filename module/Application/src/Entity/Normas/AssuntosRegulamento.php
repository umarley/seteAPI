<?php

namespace Db\Normas;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class AssuntosRegulamento extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'assuntos_regulamento';
        $this->primaryKey = 'id_assunto';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getLista(){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['marc' => $this->tableIdentifier])
                ->columns(['id_assunto', 'assunto'])
                ->order("marc.assunto ASC");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function assuntoExiste($idAssunto){
        $arLista = $this->getLista();
        $arIdAssunto = [];
        foreach ($arLista as $row){
            $arIdAssunto[] = (int) $row['id_assunto'];
        }
        $idAssunto = (int) $idAssunto;
        if(in_array($idAssunto, $arIdAssunto)){
            return true;
        }else{
            return false;
        }
    }
    

}
