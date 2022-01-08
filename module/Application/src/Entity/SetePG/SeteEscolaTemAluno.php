<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteEscolaTemAluno extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_escola_tem_alunos';
        $this->primaryKey = 'id_escola';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['esc' => new \Laminas\Db\Sql\TableIdentifier('sete_escolas', 'sete')], "eta.id_escola = esc.id_escola AND eta.codigo_cidade = esc.codigo_cidade", ['*'])
                ->where("eta.codigo_cidade = {$arIds['codigo_cidade']} AND eta.id_aluno = {$arIds['id_aluno']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }
    
    public function getAlunosByEscola($codigoCidade, $idEscola){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['al' => new \Laminas\Db\Sql\TableIdentifier('sete_alunos', 'sete')], "eta.id_aluno = al.id_aluno AND eta.codigo_cidade = al.codigo_cidade", ['codigo_cidade', 'id_aluno', 'nome', 'cpf', 'loc_latitude', 'loc_longitude', 'nivel', 'turno'])
                ->where("eta.codigo_cidade = {$codigoCidade} AND eta.id_escola = {$idEscola}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        $arLista = [];
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function getNomeEscolaAssociadaAluno($codigoCidade, $idAluno){
        $sql = "SELECT coalesce(r.nome, 'Não Informada') as nome FROM sete.sete_escola_tem_alunos raa 
                    inner join sete.sete_escolas r on raa.id_escola  = r.id_escola  and raa.codigo_cidade  = r.codigo_cidade 
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
    
    public function alunoAssociadoEscola($idAluno, $codigoCidade){
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
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND id_aluno = {$arIds['id_aluno']} AND id_escola = {$arIds['id_escola']}");
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
    
    /**
     * Método para excluir associação de escola através aluno
     */
    public function _deleteAssociacaoAluno($arIds) {
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
