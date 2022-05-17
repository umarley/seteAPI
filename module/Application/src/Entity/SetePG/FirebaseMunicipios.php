<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Adapter\Adapter;

class FirebaseMunicipios extends \Db\Core\AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'firebase_municipios';
        $this->primaryKey = 'codigo_municipio';
        $this->schema = 'api';
        parent::__construct(\Db\Core\AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getLista() {
        $sql = "select cid.nome  as nome_cidade, cid.latitude, cid.longitude, cid.codigo_ibge, cid.codigo_ibge as codigo_municipio, est.nome as nome_estado, est.uf  from 
                (select distinct codigo_cidade from sete.sete_usuarios su) municipio
                inner join sete.glb_municipio cid on municipio .codigo_cidade = cid.codigo_ibge
                inner join sete.glb_estado est on est.codigo = cid.codigo_uf ";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function getByCodigoIBGE($codigo) {
        $sql = "select cid.nome  as nome_cidade, cid.latitude, cid.longitude, cid.codigo_ibge, est.nome as nome_estado, est.uf from 
                (select distinct codigo_cidade from sete.sete_usuarios su) municipio
                inner join sete.glb_municipio cid on municipio .codigo_cidade = cid.codigo_ibge
                inner join sete.glb_estado est on est.codigo = cid.codigo_uf 
                where cid.codigo_ibge = {$codigo}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row;
    }

    public function getTotalMunicipios($busca = "") {
        $sql = "select count(*) as qtd  from 
            (select distinct codigo_cidade from sete.sete_usuarios su) municipio
            inner join sete.glb_municipio cid on municipio .codigo_cidade = cid.codigo_ibge
            inner join sete.glb_estado est on est.codigo = cid.codigo_uf ";
        if (!empty($busca)) {
            $sql .= " WHERE cid.nome LIKE '{$busca}%'";
        }
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['qtd'];
    }

    public function getMunicipiosLista($offset, $limit = 20, $busca = "") {
        $dbSeteEscolas = new \Db\SetePG\SeteEscolas();
        $dbSeteAlunos = new \Db\SetePG\SeteAlunos();
        $dbSeteVeiculos = new \Db\SetePG\SeteVeiculos();
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $dbSeteMotoristas = new \Db\SetePG\SeteMotoristas();
        $sql = "select cid.codigo_ibge AS codigo_municipio, cid.nome AS nome_cidade, est.nome AS nome_estado, est.uf  from 
                (select distinct codigo_cidade from sete.sete_usuarios su) municipio
                inner join sete.glb_municipio cid on municipio .codigo_cidade = cid.codigo_ibge
                inner join sete.glb_estado est on est.codigo = cid.codigo_uf";
        if (!empty($busca)) {
            $sql .= " WHERE cid.nome LIKE '{$busca}%'";
        }
        $sql .= " OFFSET {$offset} LIMIT {$limit}";
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
        $dbSeteEscolas = new \Db\SetePG\SeteEscolas();
        $dbSeteAlunos = new \Db\SetePG\SeteAlunos();
        $dbSeteVeiculos = new \Db\SetePG\SeteVeiculos();
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $dbSeteMotoristas = new \Db\SetePG\SeteMotoristas();
        $sql = "select cid.codigo_ibge AS codigo_municipio, cid.nome AS nome_cidade, est.nome AS nome_estado, est.uf  from 
                (select distinct codigo_cidade from sete.sete_usuarios su) municipio
                inner join sete.glb_municipio cid on municipio .codigo_cidade = cid.codigo_ibge
                inner join sete.glb_estado est on est.codigo = cid.codigo_uf
                    ORDER BY est.nome, cid.nome ASC";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $key => $row) {
            $rowArray = (array) $row;

            $arLista[] = array_merge($rowArray, [
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
