<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteRotaAtendeAluno extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_rota_atende_aluno';
        $this->primaryKey = 'id_rota';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getAlunosById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['alu' => new \Laminas\Db\Sql\TableIdentifier('sete_alunos', 'sete')], "eta.id_aluno = alu.id_aluno AND eta.codigo_cidade = alu.codigo_cidade", ['codigo_cidade', 'id_aluno', 'nome', 'cpf', 'loc_latitude', 'loc_longitude', 'nivel', 'turno'])
                ->where("eta.codigo_cidade = {$arIds['codigo_cidade']} AND eta.id_rota = {$arIds['id_rota']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arAlunos = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
            $arAlunos[] = $row;
        }
        return $arAlunos;
    }

    public function getNomeRotaAssociadoAluno($codigoCidade, $idAluno){
        $sql = "SELECT coalesce(r.nome, 'Não Informada') as nome FROM sete.sete_rota_atende_aluno raa 
                inner join sete.sete_rotas r on raa.id_rota  = r.id_rota  and raa.codigo_cidade  = r.codigo_cidade 
                WHERE raa.codigo_cidade = '{$codigoCidade}' AND raa.id_aluno = {$idAluno}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $execute = $statement->execute();
        if($execute->count() > 0){
            $row = $execute->current();
            return $row['nome'];
        }else{
            return 'Não informada';
        }
    }
    
    public function getByIdAluno($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['rot' => new \Laminas\Db\Sql\TableIdentifier('sete_rotas', 'sete')], "eta.id_rota = rot.id_rota AND eta.codigo_cidade = rot.codigo_cidade", ['*'])
                ->where("eta.codigo_cidade = {$arIds['codigo_cidade']} AND eta.id_aluno = {$arIds['id_aluno']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }
    
    public function alunoAssociadoRota($idAluno, $codigoCidade){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = '{$codigoCidade}' AND id_aluno = {$idAluno}");
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
    
    public function _deleteByAlunoAndRota($arIds) {
        $this->sql = new Sql($this->AdapterBD);
        $delete = $this->sql->delete($this->tableIdentifier);
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND id_aluno = {$arIds['id_aluno']} AND id_rota = {$arIds['id_rota']}");
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
    
    public function getQtdAlunosSemRota($codigoCidade){
        $sql = "SELECT count(*) as qtd FROM sete.sete_alunos al WHERE al.codigo_cidade  = '{$codigoCidade}'
                AND al.id_aluno not in (SELECT id_aluno FROM sete.sete_rota_atende_aluno 
                                    WHERE codigo_cidade = '{$codigoCidade}' and id_aluno = al.id_aluno)";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['qtd'];
    }
    
    public function getQtdAlunosComRota($codigoCidade){
        $sql = "SELECT count(*) as qtd FROM sete.sete_alunos al WHERE al.codigo_cidade  = '{$codigoCidade}'
                AND al.id_aluno in (SELECT id_aluno FROM sete.sete_rota_atende_aluno 
                                    WHERE codigo_cidade = '{$codigoCidade}' and id_aluno = al.id_aluno)";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['qtd'];
    }

    public function getQtdAlunosPorRota($codigoCidade){
        $sql = "SELECT id_rota, COUNT(*) as qtd 
                FROM sete.sete_rota_atende_aluno rota 
                WHERE rota.codigo_cidade  = 3540606
                GROUP BY id_rota
                ORDER BY id_rota";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function getAllLocatedAlunos($codigoCidade){
        $sql = "SELECT * FROM sete.sete_alunos al WHERE al.codigo_cidade  = '{$codigoCidade}'
                AND loc_latitude IS NOT NULL AND loc_longitude IS NOT NULL";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

}
