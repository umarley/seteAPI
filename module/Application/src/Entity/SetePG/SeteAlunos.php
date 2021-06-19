<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteAlunos extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_alunos';
        $this->primaryKey = 'id_aluno';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function qtdAlunosAtendidos($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }    
    
    

}
