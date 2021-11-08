<?php

namespace Db\Core;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Predicate\Expression;

class CargaDados extends AbstractDatabase {

    const CARGA_MUNICIPIOS = 'FIREBASE_MUNICIPIOS';
    const CARGA_USERS = 'FIREBASE_USERS';

    public function __construct() {
        $this->table = 'sys_carga_dados';
        $this->primaryKey = 'carga';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }

    public function podeExecutarCargaDados($codCarga) {
        $sql = "SELECT (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(data_carga)) AS tempo_percorrido, tempo FROM sys_carga_dados cd 
                    WHERE cd.carga = '{$codCarga}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        if ($row['tempo_percorrido'] > $row['tempo']) {
            return true;
        } else {
            return false;
        }
    }
    
    public function executarCargaProcessamento(){
        $sql = "CALL pLoadCidadesProcessar()";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $statement->execute();
    }

}
