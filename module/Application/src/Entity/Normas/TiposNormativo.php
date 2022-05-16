<?php

namespace Db\Normas;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class TiposNormativo extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'tipos_normativo';
        $this->primaryKey = 'id_tipo';
        $this->schema = 'normas';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getLista(){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['marc' => $this->tableIdentifier])
                ->columns(['id_tipo', 'nm_tipo'])
                ->order("marc.nm_tipo ASC");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function tipoExiste($idTipo){
        $arLista = $this->getLista();
        $arIdTipo = [];
        foreach ($arLista as $row){
            $arIdTipo[] = (int) $row['id_tipo'];
        }
        $idTipo = (int) $idTipo;
        if(in_array($idTipo, $arIdTipo)){
            return true;
        }else{
            return false;
        }
    }
    

}
