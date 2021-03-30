<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class SeteUsuarios extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sete_usuarios';
        $this->primaryKey = 'uid';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function usuarioExiste($uid){
        $sql = "SELECT COUNT(*) AS qtd FROM sete_usuarios us
                    WHERE us.uid = '{$uid}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getUsuariosPendentesLiberacao($offset, $limit = 50){
        $sql = "SELECT us.uid, us.nome, codigo_cidade, concat(cidade, ' - ', estado) AS localidade, email  FROM sete_usuarios us
                    WHERE us.uid NOT IN (SELECT uid FROM sete_usuarios_liberados ul)
                    LIMIT {$offset}, {$limit}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function getTotalUsuariosPendentesLiberacao(){
        $sql = "SELECT COUNT(*) AS QTD FROM sete_usuarios us
                    WHERE us.uid NOT IN (SELECT uid FROM sete_usuarios_liberados ul)";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['QTD'];
    }
}
