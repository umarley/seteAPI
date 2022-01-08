<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteRotaAtendidaPorMonitor extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_rota_atendida_por_monitor';
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
    
    public function getByCPFMonitor($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['rot' => new \Laminas\Db\Sql\TableIdentifier('sete_rotas', 'sete')], "eta.id_rota = rot.id_rota AND eta.codigo_cidade = rot.codigo_cidade", ['*'])
                ->where("eta.codigo_cidade = {$arIds['codigo_cidade']} AND eta.cpf_monitor = '{$arIds['cpf_monitor']}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row){
           $arLista[] = $row; 
        }
        return $arLista;
    }
    
    public function monitorAssociadoRota($cpfMonitor, $codigoCidade, $idRota){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = '{$codigoCidade}' AND cpf_monitor = '{$cpfMonitor}' AND id_rota = {$idRota}");
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
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND cpf_monitor = '{$arIds['cpf_monitor']}' AND id_rota = {$arIds['id_rota']}");
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
