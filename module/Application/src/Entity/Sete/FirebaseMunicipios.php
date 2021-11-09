<?php

namespace Db\Sete;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Adapter\Adapter;

class FirebaseMunicipios extends AbstractDatabase {

    public function __construct() {
        $this->table = 'firebase_municipios';
        $this->primaryKey = 'codigo_municipio';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function __destruct() {
        $this->closeConnection();
    }

    public function getLista() {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->columns(['codigo_municipio'])
                ->join(['cid' => new TableIdentifier('glb_municipio')], "us.codigo_municipio = cid.codigo_ibge", ['nome_cidade' => 'nome', 'latitude', 'longitude', 'codigo_ibge'])
                ->join(['est' => new TableIdentifier('glb_estado')], "est.codigo = cid.codigo_uf", ['nome_estado' => 'nome', 'uf']);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function getListaProcessar() {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => new TableIdentifier('firebase_processamento_cidades')])
                ->columns(['codigo_municipio' => 'codigo_cidade'])
                ->join(['cid' => new TableIdentifier('glb_municipio')], "us.codigo_cidade = cid.codigo_ibge", ['nome_cidade' => 'nome', 'latitude', 'longitude', 'codigo_ibge'])
                ->join(['est' => new TableIdentifier('glb_estado')], "est.codigo = cid.codigo_uf", ['nome_estado' => 'nome', 'uf'])
                ->where("us.is_processado = 'N'")
                ->limit("100");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arLista = [];
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    public function marcarProcessado($codigoCidade){
        $this->sql = new Sql($this->AdapterBD);
        $update = $this->sql->update(new TableIdentifier('firebase_processamento_cidades'));
        $update->set(['dt_processado' => date("Y-m-d H:i:s"), 'is_processado' => 'S']);
        $update->where(['codigo_cidade' => $codigoCidade]);
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

    public function getByCodigoIBGE($codigo) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->join(['cid' => new TableIdentifier('glb_municipio')], "us.codigo_municipio = cid.codigo_ibge", ['nome_cidade' => 'nome', 'latitude', 'longitude', 'codigo_ibge'])
                ->join(['est' => new TableIdentifier('glb_estado')], "est.codigo = cid.codigo_uf", ['nome_estado' => 'nome', 'uf'])
                ->where("cid.codigo_ibge = {$codigo}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        return $prepare->execute()->current();
    }

    public function getTotalMunicipios($busca = "") {
        $sql = "SELECT COUNT(*) AS qtd FROM firebase_municipios us     
                    INNER JOIN glb_municipio mun ON us.codigo_municipio = mun.codigo_ibge
                    INNER JOIN glb_estado est ON est.codigo = mun.codigo_uf";
        if(!empty($busca)){
            $sql .= " WHERE mun.nome LIKE '{$busca}%'";
        }
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['qtd'];
    }

    public function getMunicipiosLista($offset, $limit = 20, $busca = "") {
        $dbSeteEscolas = new \Db\Sete\SeteEscolas();
        $dbSeteAlunos = new \Db\Sete\SeteAlunos();
        $dbSeteVeiculos = new \Db\Sete\SeteVeiculos();
        $dbSeteRotas = new \Db\Sete\SeteRotas();
        $dbSeteMotoristas = new \Db\Sete\SeteMotoristas();
        $sql = "SELECT mun.codigo_ibge AS codigo_municipio, mun.nome AS nome_cidade, est.nome AS nome_estado, est.uf FROM firebase_municipios us
                    INNER JOIN glb_municipio mun ON us.codigo_municipio = mun.codigo_ibge
                    INNER JOIN glb_estado est ON est.codigo = mun.codigo_uf";
        if(!empty($busca)){
            $sql .= " WHERE mun.nome LIKE '{$busca}%'";
        }
        $sql .= " LIMIT {$offset}, {$limit}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $key => $row) {
            $arLista[$key] = $row;
            $arLista[$key]['data'] = [
                'n_escolas' => $dbSeteEscolas->qtdEscolasAtendidas($row['codigo_municipio']),
                'n_alunos' => $dbSeteAlunos->qtdAlunosAtendidos($row['codigo_municipio']),
                'n_veiculos_funcionamento' => $dbSeteVeiculos->qtdVeiculosFuncionando($row['codigo_municipio']),
                'n_veiculos_manutencao' => $dbSeteVeiculos->qtdVeiculosManutencao($row['codigo_municipio']),
                'n_rotas' => $dbSeteRotas->qtdRotas($row['codigo_municipio']),
                'n_rotas_kilometragem_total' => $dbSeteRotas->qtdRotasKilometragemTotal($row['codigo_municipio']),
                'n_rotas_kilometragem_media' => $dbSeteRotas->qtdRotasKilometragemMedia($row['codigo_municipio']),
                'n_motoristas' => $dbSeteMotoristas->qtdMotoristas($row['codigo_municipio']),
                'n_tempo_medio_rota' => $dbSeteRotas->qtdRotasTempoMedio($row['codigo_municipio'])
            ];
        }
        return $arLista;
    }
    
    public function getMunicipiosListaExcel() {
        $dbSeteEscolas = new \Db\Sete\SeteEscolas();
        $dbSeteAlunos = new \Db\Sete\SeteAlunos();
        $dbSeteVeiculos = new \Db\Sete\SeteVeiculos();
        $dbSeteRotas = new \Db\Sete\SeteRotas();
        $dbSeteMotoristas = new \Db\Sete\SeteMotoristas();
        $sql = "SELECT mun.codigo_ibge AS codigo_municipio, mun.nome AS nome_cidade, est.nome AS nome_estado, est.uf FROM firebase_municipios us
                    INNER JOIN glb_municipio mun ON us.codigo_municipio = mun.codigo_ibge
                    INNER JOIN glb_estado est ON est.codigo = mun.codigo_uf
                    ORDER BY est.nome, mun.nome ASC";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $key => $row) {
            $rowArray = (array) $row;
            
            $arLista[] = array_merge($rowArray,  [
                'n_escolas' => !empty($dbSeteEscolas->qtdEscolasAtendidas($row['codigo_municipio'])) ? $dbSeteEscolas->qtdEscolasAtendidas($row['codigo_municipio']) : '0',
                'n_alunos' => !empty($dbSeteAlunos->qtdAlunosAtendidos($row['codigo_municipio'])) ? $dbSeteAlunos->qtdAlunosAtendidos($row['codigo_municipio']) : '0',
                'n_veiculos_funcionamento' => !empty($dbSeteVeiculos->qtdVeiculosFuncionando($row['codigo_municipio'])) ? $dbSeteVeiculos->qtdVeiculosFuncionando($row['codigo_municipio']) : '0',
                'n_veiculos_manutencao' => !empty($dbSeteVeiculos->qtdVeiculosManutencao($row['codigo_municipio'])) ? $dbSeteVeiculos->qtdVeiculosManutencao($row['codigo_municipio']) : '0',
                'n_rotas' => !empty($dbSeteRotas->qtdRotas($row['codigo_municipio'])) ? $dbSeteRotas->qtdRotas($row['codigo_municipio']) : '0',
                'n_rotas_kilometragem_total' => !empty($dbSeteRotas->qtdRotasKilometragemTotal($row['codigo_municipio'])) ? $dbSeteRotas->qtdRotasKilometragemTotal($row['codigo_municipio']) : '0',
                'n_rotas_kilometragem_media' => !empty($dbSeteRotas->qtdRotasKilometragemMedia($row['codigo_municipio'])) ? $dbSeteRotas->qtdRotasKilometragemMedia($row['codigo_municipio']) : '0',
                'n_motoristas' => !empty($dbSeteMotoristas->qtdMotoristas($row['codigo_municipio'])) ? $dbSeteMotoristas->qtdMotoristas($row['codigo_municipio']) : '0',
                'n_tempo_medio_rota' => !empty($dbSeteRotas->qtdRotasTempoMedio($row['codigo_municipio'])) ? $dbSeteRotas->qtdRotasTempoMedio($row['codigo_municipio']) : '0'
            ]);
        }
        return $arLista;
    }

}
