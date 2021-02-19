<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class MunicipiosUsandoSistema extends AbstractDatabase {

    public function __construct() {
        $this->table = 'cidades_usando_sistema';
        $this->primaryKey = 'codigo_ibge';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function getLista(){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->join(['cid' => new TableIdentifier('glb_municipio')], "us.codigo_ibge = cid.codigo_ibge", ['nome_cidade' => 'nome', 'latitude', 'longitude', 'codigo_ibge'])
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
                ->join(['cid' => new TableIdentifier('glb_municipio')], "us.codigo_ibge = cid.codigo_ibge", ['nome_cidade' => 'nome', 'latitude', 'longitude', 'codigo_ibge'])
                ->join(['est' => new TableIdentifier('glb_estado')], "est.codigo = cid.codigo_uf", ['nome_estado' => 'nome', 'uf'])
                ->where("cid.codigo_ibge = {$codigo}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        return $prepare->execute()->current();
    }
    

}
