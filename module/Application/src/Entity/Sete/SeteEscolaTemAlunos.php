<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteEscolaTemAlunos extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_escola_tem_alunos';
        $this->primaryKey = 'id_escola';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    
    

}
