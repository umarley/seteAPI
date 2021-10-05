<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteEscolas extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_escolas';
        $this->primaryKey = 'id_escola';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }

    public function __destruct() {
        $this->closeConnection();
    }

    public function getIdEscolaByIdFirebase($municipio, $idFirebase) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_escola'])
                ->where("codigo_escola_firebase = '{$idFirebase}'")
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute();
        if ($row->count() > 0) {
            $row = $row->current();
            return $row['id_escola'];
        } else {
            return false;
        }
    }

    public function qtdEscolasAtendidas($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute();
        if ($row->count() > 0) {
            $row = $row->current();
            return $row['qtd'];
        } else {
            return 0;
        }
    }

}
