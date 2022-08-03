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
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getAssuntoByNorma($idNorma){
        $sql = "select na.id_assunto,  ar.assunto, na.outro_assunto
                from normas.normas_assunto na 
                inner join normas.assuntos_regulamento ar on ar.id_assunto = na.id_assunto 
                where na.id_norma = {$idNorma}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $rowAssunto){
            $arLista[] = $rowAssunto;
        }
        return $arLista;
    }
    

}
