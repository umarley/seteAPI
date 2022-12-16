<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use PHPUnit\Framework\Constraint\IsEmpty;

use function PHPUnit\Framework\isEmpty;

class SeteVeiculos extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_veiculos';
        $this->primaryKey = 'id_veiculo';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['v' => $this->tableIdentifier])
                ->columns(['*'])
                ->join(['marca' => new TableIdentifier('glb_marca_veiculos', 'sete')], "marca.id_marca = v.marca", ['marca_str' => 'nm_marca'], "LEFT")
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND id_veiculo = {$arIds['id_veiculo']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function getLista($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_veiculo', 'placa','modelo','tipo','capacidade','marca','origem','manutencao'])
                ->where("codigo_cidade = {$municipio}");
        $arLista = [];
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }


    public function veiculoExiste($placa, $codigoCidade) {
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
    
    public function veiculoExisteById($idVeiculo, $codigoCidade) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("id_veiculo = '{$idVeiculo}' AND codigo_cidade = '{$codigoCidade}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function veiculoExistePUT($placa, $codigoCidade, $idVeiculo) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("placa = '{$placa}' AND codigo_cidade = '{$codigoCidade}' AND id_veiculo <> {$idVeiculo}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
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
        $update->where(["codigo_cidade" => $arId['codigo_cidade'], 'id_veiculo' => $arId['id_veiculo']]);
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
        $delete->where(["codigo_cidade" => $arIds['codigo_cidade'], 'id_veiculo' => $arIds['id_veiculo']]);
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
    
    public function qtdVeiculosFuncionando($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}")
                ->where("manutencao = 'N'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }  
    
    public function qtdVeiculosManutencao($municipio){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}")
                ->where("manutencao = 'S'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    } 

    public function getDadosDashboardVeiculos($codigoCidade){
        $sql = "SELECT DISTINCT 
        (SELECT count(*) FROM sete.sete_veiculos WHERE codigo_cidade = '{$codigoCidade}') AS total,
        (SELECT count(*) FROM sete.sete_veiculos WHERE codigo_cidade = '{$codigoCidade}' AND manutencao='S') AS manutencao,
        (SELECT count(*) FROM (SELECT DISTINCT id_veiculo FROM(SELECT DISTINCT veiculo.id_veiculo, mot.nome FROM sete.sete_motoristas mot
            JOIN  sete.sete_rota_dirigida_por_motorista rotamot ON mot.codigo_cidade = rotamot.codigo_cidade 
            JOIN sete.sete_rota_possui_veiculo rotaveiculo ON rotamot.codigo_cidade = rotaveiculo.codigo_cidade  
            JOIN sete.sete_veiculos veiculo ON rotaveiculo.codigo_cidade = veiculo.codigo_cidade
            where mot.codigo_cidade = '{$codigoCidade}') veic_mot) qnt_veic) AS com_motorista,
        (SELECT count(*) FROM (SELECT * FROM sete.sete_veiculos WHERE codigo_cidade = '{$codigoCidade}' AND modo='1') modo) AS caminho_da_escola
        FROM sete.sete_veiculos WHERE codigo_cidade = '{$codigoCidade}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row;
    }

}
