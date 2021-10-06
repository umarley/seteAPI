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

}
