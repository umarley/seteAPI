<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteRotaPassaPorEscola extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_rota_passa_por_escolas';
        $this->primaryKey = 'id_rota';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['rot' => new \Laminas\Db\Sql\TableIdentifier('sete_rotas', 'sete')], "eta.id_rota = rot.id_rota AND eta.codigo_cidade = rot.codigo_cidade", ['*'])
                ->where("eta.codigo_cidade = {$arIds['codigo_cidade']} AND eta.id_rota = {$arIds['id_rota']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }
        
    public function getEscolaByRotas($codigoCidade, $idRota){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['al' => new \Laminas\Db\Sql\TableIdentifier('sete_escolas', 'sete')], "eta.id_escola = al.id_escola AND eta.codigo_cidade = al.codigo_cidade", ['nome', 'loc_latitude', 'loc_longitude', 'horario_matutino', 'horario_vespertino', 'horario_noturno', 'ensino_medio', 'ensino_fundamental', 'ensino_superior', 'ensino_pre_escola', 'mec_tp_localizacao'])
                ->where("eta.codigo_cidade = {$codigoCidade} AND eta.id_rota = {$idRota}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        $arLista = [];
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function getRotasByEscola($codigoCidade, $idEscola){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['al' => new \Laminas\Db\Sql\TableIdentifier('sete_rotas', 'sete')], "eta.id_rota = al.id_rota AND eta.codigo_cidade = al.codigo_cidade", ['codigo_cidade', 'id_rota', 'nome', 'km', 'turno_matutino', 'turno_vespertino', 'turno_noturno'])
                ->where("eta.codigo_cidade = {$codigoCidade} AND eta.id_escola = {$idEscola}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        $arLista = [];
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function getLista($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['codigo_cidade', 'id_escola', 'nome'])
                ->where("codigo_cidade = {$municipio}");
        $arLista = [];
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function rotaAssociadoEscola($idRota, $codigoCidade){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = '{$codigoCidade}' AND id_rota = {$idRota}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function _delete($arIds) {
        $this->sql = new Sql($this->AdapterBD);
        $delete = $this->sql->delete($this->tableIdentifier);
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND id_rota = {$arIds['id_rota']} AND id_escola = {$arIds['id_escola']}");
        $sql = $this->sql->buildSqlString($delete);
        try {
            $this->AdapterBD->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $boResultado = true;
            $message = "Registro excluido com sucesso!";
        } catch (\PDOException $zAdapterEx) {
            $boResultado = false;
            $message = "Falha ao excluir o registro. Contacte o administrador do sistema para maiores informações. <br />" . $zAdapterEx->getMessage();
        } catch (\Laminas\Db\Adapter\Exception\InvalidQueryException $zendDbExc) {
            $boResultado = false;
            $message = "Falha ao excluir o registro. Contacte o administrador do sistema para maiores informações. <br />" . $zendDbExc->getMessage();
        }
        return ['result' => $boResultado, 'messages' => $message];
    }

    public function getDadosDashboardEscolas($codigoCidade){
        $sql = "SELECT DISTINCT
        (SELECT count(*) FROM sete.sete_escolas WHERE codigo_cidade  = '{$codigoCidade}') AS total,
        (SELECT count(*) FROM (SELECT DISTINCT id_escola FROM sete.sete_rota_passa_por_escolas WHERE codigo_cidade  = '{$codigoCidade}') escolasrotas) AS com_rota,
        (SELECT count(*) FROM sete.sete_escolas WHERE codigo_cidade  = '{$codigoCidade}' AND mec_tp_localizacao=2) AS rural,
        (SELECT count(*) FROM sete.sete_escolas WHERE codigo_cidade  = '{$codigoCidade}' AND mec_tp_localizacao=1) AS urbana
        FROM sete.sete_escolas WHERE codigo_cidade  = '{$codigoCidade}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row;
    }

}
