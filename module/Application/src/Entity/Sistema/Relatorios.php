<?php

namespace Db\Sistema;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;

class Relatorios extends AbstractDatabasePostgres {

    const TIPO_DATA = 'date';
    const TIPO_DATARANGE = 'date_range';
    const TIPO_DATASQL = 'dateSQL';
    const TIPO_SELECT = 'select';
    const TIPO_TEXTO = 'text';
    const TIPO_HIDDEN = 'hidden';
    
    const NAO_REQUERIDO = "N";
    const REQUERIDO = "S";
    
    public function __construct() {
        $this->table = 'relatorios';
        $this->primaryKey = 'id_relatorio';
        $this->schema = 'sistema';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getDBConfig(){
        return $this->dbConfig;
    }
    
    public function getLista(){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_relatorio', 'nome', 'descricao']);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function getParametrosRelatorio($idRelatorio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(new TableIdentifier('relatorios_parametros', 'sistema'))
                ->columns(['parametro', 'tipo', 'label', 'is_obrigatorio']);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function parametroIsRequerido($idRelatorio, $nomeParametro){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(new TableIdentifier('relatorios_parametros', 'sistema'))
                ->columns(['is_requerido'])
                ->where("id_relatorio  = {$idRelatorio} and parametro = '{$nomeParametro}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if($row['is_requerido'] == self::REQUERIDO){
            return true;            
        }else{
            return false;
        }
    }
    
    public function parametroExiste($idRelatorio, $nomeParametro){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(new TableIdentifier('relatorios_parametros', 'sistema'))
                ->columns(['is_requerido'])
                ->where("id_relatorio  = {$idRelatorio} and parametro = '{$nomeParametro}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->count();
        if($row > 0){
            return true;
        }else{
            return false;
        }
    }
    
    public function getParametroByRelatorioAndNome($idRelatorio, $nomeParametro){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(new TableIdentifier('relatorios_parametros', 'sistema'))
                ->columns(['*'])
                ->where("id_relatorio  = {$idRelatorio} and parametro = '{$nomeParametro}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function getById($idRelatorio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("id_relatorio = {$idRelatorio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

}
