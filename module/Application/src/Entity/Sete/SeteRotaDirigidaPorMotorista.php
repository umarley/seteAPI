<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteRotaDirigidaPorMotorista extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_rota_dirigida_por_motorista';
        $this->primaryKey = 'id_rota';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    
    

}
