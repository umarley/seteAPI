<?php

namespace Db\Normas;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class NormasAssunto extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'normas_assunto';
        $this->primaryKey = 'id_norma';
        $this->schema = 'normas';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    

}
