<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteVeiculos extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_veiculos';
        $this->primaryKey = 'id_veiculo';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND id_veiculo = {$arIds['id_veiculo']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function getLista($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['codigo_cidade','id_veiculo', 'placa','modelo'])
                ->where("codigo_cidade = {$municipio}");
        $arLista = [];
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function qtdAlunosAtendidos($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }

    public function veiculoExiste($placa, $codigoCidade, $idVeiculo = null) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("placa = '{$placa}' AND codigo_cidade = '{$codigoCidade}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getUltimoIdInserido() {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id' => new \Laminas\Db\Sql\Expression("max(id_veiculo)")]);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['id'];
    }

    public function _atualizar($arId, $dados) {
        $this->sql = new Sql($this->AdapterBD);
        $update = $this->sql->update($this->tableIdentifier);
        $update->set($dados);
        $update->where(["codigo_cidade" => $arId['codigo_cidade'], 'id_escola' => $arId['id_escola']]);
        $sql = $this->sql->buildSqlString($update);
        try {
            $this->AdapterBD->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $bool = true;
            $message = 'Registro atualizado com sucesso!';
        } catch (\PDOException $ex) {
            $bool = false;
            $message = "Falha ao atualizar o registro. " . $ex->getMessage();
            echo $ex->getMessage();
            die();
            //$this->rollback();
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $ex) {
            $bool = false;
            $message = "Falha ao atualizar o registro. " . $ex->getMessage();
            //$this->rollback();
        }
        return ['result' => $bool, 'messages' => $message];
    }

    public function _delete($arIds) {
        $this->sql = new Sql($this->AdapterBD);
        $delete = $this->sql->delete($this->tableIdentifier);
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND id_escola = {$arIds['id_escola']}");
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
