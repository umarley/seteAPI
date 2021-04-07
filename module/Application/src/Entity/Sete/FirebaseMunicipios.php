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

    public function getLista() {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
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

    public function getByCodigoIBGE($codigo) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->join(['cid' => new TableIdentifier('glb_municipio')], "us.codigo_municipio = cid.codigo_ibge", ['nome_cidade' => 'nome', 'latitude', 'longitude', 'codigo_ibge'])
                ->join(['est' => new TableIdentifier('glb_estado')], "est.codigo = cid.codigo_uf", ['nome_estado' => 'nome', 'uf'])
                ->where("cid.codigo_ibge = {$codigo}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        return $prepare->execute()->current();
    }

    public function getTotalMunicipios() {
        $sql = "SELECT COUNT(*) AS qtd FROM firebase_municipios us     
                    INNER JOIN glb_municipio mun ON us.codigo_municipio = mun.codigo_ibge
                    INNER JOIN glb_estado est ON est.codigo = mun.codigo_uf";
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
            $sql .= " WHERE mun.nome LIKE '%{$busca}%'";
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
                'n_motoristas' => $dbSeteMotoristas->qtdMotoristas($row['codigo_municipio'])
            ];
        }
        return $arLista;
    }

}
