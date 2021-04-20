<?php

namespace Db\API;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class ApiLiberacaoUsuario extends AbstractDatabase {

    public function __construct() {
        $this->table = 'api_liberacao_usuario';
        $this->primaryKey = 'id_liberacao';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    

}
