<?php

namespace Db\Normas;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class Normas extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'normas';
        $this->primaryKey = 'id';
        $this->schema = 'normas';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getLista($codigoCidade){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['marc' => $this->tableIdentifier])
                ->columns(['id', 'numero_norma', 'titulo', 'id_tipo', 'outro_tipo'])
                ->where("marc.codigo_cidade = {$codigoCidade}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function getById($arIds) {
        $dbNormasAssunto = new \Db\Normas\NormasAssunto();
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['v' => $this->tableIdentifier])
                ->columns(['id', 'numero_norma', 'titulo','id_tipo', 'tipo_veiculo', 'outro_tipo', 'codigo_cidade'])
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND id = {$arIds['id_norma']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        $row['assuntos'] = $dbNormasAssunto->getAssuntoByNorma($row['id']);
        return $row;
    }
    
    public function getConteudoPDF($idNorma) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['v' => $this->tableIdentifier])
                ->columns(['arquivo_pdf'])
                ->where("id = {$idNorma}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['arquivo_pdf'];
    }
    
    public function getUltimoIdInserido(){
        $sql = "select max(id) as id from normas.normas n";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $id = $statement->execute()->current();
        return $id['id'];
    }
    
    

}
