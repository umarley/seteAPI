<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteVeiculos extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_veiculos';
        $this->primaryKey = 'id_veiculo';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }

    public function __destruct() {
        $this->closeConnection();
    }

    public function getIdVeiculoByIdFirebase($municipio, $idFirebase) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_veiculo'])
                ->where("id_firebase = '{$idFirebase}'")
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['id_veiculo'];
    }

    public function qtdVeiculosFuncionando($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}")
                ->where("manutencao = 'N'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }

    public function qtdVeiculosManutencao($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}")
                ->where("manutencao = 'S'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }

}
