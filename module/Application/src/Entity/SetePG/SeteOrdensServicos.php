<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use PHPUnit\Framework\Constraint\IsEmpty;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class SeteOrdensServicos extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_ordem_servico';
        $this->primaryKey = 'id_ordem';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND id_ordem= {$arIds['id_ordem']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function getLista($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_ordem','id_veiculo', 'id_fornecedor','data','tipo_servico','comentario','termino'])
                ->where("codigo_cidade = {$municipio}");
        $arLista = [];
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }


    public function veiculoExisteById($idVeiculo, $codigoCidade) {
        $sql = "select count(*)
                as qntd
                from sete.sete_veiculos a
                where  id_veiculo = '{$idVeiculo}' AND codigo_cidade = '{$codigoCidade}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        if ($row['qntd'] != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function fornecedorExisteById($idFornecedor, $codigoCidade) {
        $sql = "select count(*)
                as qntd
                from sete.sete_fornecedores a
                where  id_fornecedor = '{$idFornecedor}' AND codigo_cidade = '{$codigoCidade}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        if ($row['qntd'] != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getUltimoIdInserido() {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id' => new \Laminas\Db\Sql\Expression("max(id_ordem)")]);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['id'];
    }

    

    public function veiculoExisteUnico($placa, $codigoCidade, $idVeiculo) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("placa = '{$placa}' AND codigo_cidade = '{$codigoCidade}' AND id_veiculo != '{$idVeiculo}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function _atualizar($arId, $dados) {
        $this->sql = new Sql($this->AdapterBD);
        $update = $this->sql->update($this->tableIdentifier);
        $update->set($dados);
        $update->where(["codigo_cidade" => $arId['codigo_cidade'], 'id_ordem' => $arId['id_ordem']]);
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
        $delete->where(["codigo_cidade" => $arIds['codigo_cidade'], 'id_ordem' => $arIds['id_ordem']]);
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
