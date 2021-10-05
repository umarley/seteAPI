<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteRotaPossuiVeiculo extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_rota_possui_veiculo';
        $this->primaryKey = 'id_rota';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function __destruct() {
        $this->closeConnection();
    }
}
