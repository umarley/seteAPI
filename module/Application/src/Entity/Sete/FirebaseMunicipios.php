<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Adapter\Adapter;

class FirebaseMunicipios extends AbstractDatabase {

    public function __construct() {
        $this->table = 'firebase_municipios';
        $this->primaryKey = 'codigo_municipio';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function getLista(){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->join(['cid' => new TableIdentifier('glb_municipio')], "us.codigo_municipio = cid.codigo_ibge", ['nome_cidade' => 'nome', 'latitude', 'longitude', 'codigo_ibge'])
                ->join(['est' => new TableIdentifier('glb_estado')], "est.codigo = cid.codigo_uf", ['nome_estado' => 'nome', 'uf']);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function getByCodigoIBGE($codigo){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->join(['cid' => new TableIdentifier('glb_municipio')], "us.codigo_municipio = cid.codigo_ibge", ['nome_cidade' => 'nome', 'latitude', 'longitude', 'codigo_ibge'])
                ->join(['est' => new TableIdentifier('glb_estado')], "est.codigo = cid.codigo_uf", ['nome_estado' => 'nome', 'uf'])
                ->where("cid.codigo_ibge = {$codigo}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        return $prepare->execute()->current();
    }
    

}
