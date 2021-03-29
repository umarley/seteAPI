<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteAlunos extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_alunos';
        $this->primaryKey = 'id_aluno';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function getIdAlunoByFirebaseAndCodigoMunicipio($codigoMunicipio, $idFirebase){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['a' => $this->tableIdentifier])
                ->columns(['id_aluno'])
                ->where("a.codigo_aluno_firebase = '{$idFirebase}'")
                ->where("a.codigo_cidade = {$codigoMunicipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['id_aluno'];
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
