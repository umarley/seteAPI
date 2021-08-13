<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteRotaPossuiVeiculo extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_rota_possui_veiculo';
        $this->primaryKey = 'id_escola';
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
    
    public function getAlunosByEscola($codigoCidade, $idEscola){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['al' => new \Laminas\Db\Sql\TableIdentifier('sete_alunos', 'sete')], "eta.id_aluno = al.id_aluno AND eta.codigo_cidade = al.codigo_cidade", ['codigo_cidade', 'id_aluno', 'nome', 'cpf'])
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
    
    public function rotaAssociadoVeiculo($idVeiculo, $codigoCidade){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = '{$codigoCidade}' AND id_veiculo = {$idVeiculo}");
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
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND id_rota = {$arIds['id_rota']}");
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

}
