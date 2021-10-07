<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteAlunos extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_alunos';
        $this->primaryKey = 'id_aluno';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND id_aluno = {$arIds['id_aluno']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function getLista($municipio) {
        $sql = "select a.codigo_cidade, a.id_aluno, a.nome, cpf, a.loc_latitude, a.loc_longitude, a.nivel, a.turno,
                    coalesce(esc.nome, 'Não Informada') as escola,
                    coalesce(rta.nome, 'Não Informada') as rota
                    from sete.sete_alunos a 
                    left join sete.sete_escola_tem_alunos eta on a.id_aluno = eta.id_aluno and a.codigo_cidade  = eta.codigo_cidade
                    left join sete.sete_escolas esc on esc.id_escola  = eta.id_escola and esc.codigo_cidade  = eta.codigo_cidade 
                    left join sete.sete_rota_atende_aluno raa on raa.id_aluno = a.id_aluno and raa.codigo_cidade = a.codigo_cidade 
                    left join sete.sete_rotas rta on rta.id_rota = raa.id_rota and rta.codigo_cidade = raa.codigo_cidade 
                    where a.codigo_cidade  = {$municipio}";
        $arLista = [];
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        //$execute = $statement->execute();
        $this->getResultSet($statement->execute());
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

    public function alunoExiste($cpf, $idAluno = null) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("cpf = '{$cpf}'");
        $sqlBuild = $sql->buildSqlString($select);
        if($idAluno != ""){
           $sqlBuild .= " AND id_aluno <> {$idAluno}";
        }
        $statement = $this->AdapterBD->createStatement($sqlBuild);
        $statement->prepare();
        $row = $statement->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getUltimoIdInserido() {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id' => new \Laminas\Db\Sql\Expression("max(id_aluno)")]);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['id'];
    }

    public function _atualizar($arId, $dados) {
        $this->sql = new Sql($this->AdapterBD);
        $update = $this->sql->update($this->tableIdentifier);
        $update->set($dados);
        $update->where(["codigo_cidade" => $arId['codigo_cidade'], 'id_aluno' => $arId['id_aluno']]);
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
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND id_aluno = {$arIds['id_aluno']}");
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
