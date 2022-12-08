<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteEscolas extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_escolas';
        $this->primaryKey = 'id_escola';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND id_escola = {$arIds['id_escola']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function getByCodEntidadeMec($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND mec_co_entidade = {$arIds['mec_co_entidade']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function getIdEscolaByCodigoMecAndCodigoCidade($codigoEntidadeMec, $codigoCidade) {
        $sql = "SELECT id_escola FROM sete.sete_escolas e WHERE e.codigo_cidade = '{$codigoCidade}'
                    and e.mec_co_entidade = '{$codigoEntidadeMec}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['id_escola'];
    }

    public function getQuantidadeEscolasPorLocalidades($codigoCidade) {
        $sql = "select 
                case when mec_tp_localizacao = 1 then 'Urbana'
                when mec_tp_localizacao = 2 then 'Rural' end as localidade, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                group by 1";
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

    public function getQuantidadeEscolasPorDependencia($codigoCidade) {
        $sql = "select 
                case when mec_tp_dependencia = 1 then 'Federal'
                when mec_tp_dependencia = 2 then 'Estadual'
                when mec_tp_dependencia = 3 then 'Municipal'
                when mec_tp_dependencia = 4 then 'Privada' end dependencia, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                group by 1";
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

    public function getQuantidadeEscolasPorNivelEnsino($codigoCidade) {
        $sql = "select 
                    'Pré Escola' as nivel_ensino, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                    and sesc.ensino_pre_escola = 'S'
                    union 
                    select 
                    'Ensino Fundamental' as nivel_ensino, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                    and sesc.ensino_fundamental = 'S'
                    union 
                    select 
                    'Ensino Médio' as nivel_ensino, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                    and sesc.ensino_medio = 'S'
                    union 
                    select 
                    'Ensino Superior' as nivel_ensino, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                    and sesc.ensino_superior = 'S'";
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

    public function getQuantidadeEscolasPorTipoEnsino($codigoCidade) {
        $sql = "select 
                    'Ensino Regular' as regime_ensino, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                    and sesc.mec_in_regular = 'S'
                    union 
                    select 
                    'EJA' as regime_ensino, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                    and sesc.mec_in_eja = 'S'
                    union 
                    select 
                    'Ensino Profissionalizante' as regime_ensino, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                    and sesc.mec_in_profissionalizante = 'S'
                    union 
                    select 
                    'Ensino Especial' as regime_ensino, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                    and sesc.mec_in_especial_exclusiva = 'S'";
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

    public function getQuantidadeEscolasPorHorarioFuncionamento($codigoCidade) {
        $sql = "select 
                'Matutino' as horario_funcionamento, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.horario_matutino = 'S'
                union 
                select 
                'Vespertino' as horario_funcionamento, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.horario_vespertino = 'S'
                union 
                select 
                'Noturno' as horario_funcionamento, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.horario_noturno = 'S'
                union 
                select 
                'Integral' as horario_funcionamento, count(*) as qtd from sete.sete_escolas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.horario_integral = 'S'";
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
    
    public function getQuantidadeVeiculosPorCategoria($codigoCidade) {
        $sql = "select 
                case when v.tipo = 1 then 'Ônibus'
                when  v.tipo = 2 then 'Micro-Ônibus'
                when  v.tipo = 3 then 'Van'
                when  v.tipo = 4 then 'Kombi'
                when  v.tipo = 5 then 'Caminhão'
                when  v.tipo = 6 then 'Caminhonete'
                when  v.tipo = 7 then 'Motocicleta'
                when  v.tipo = 8 then 'Animal de Tração'
                when  v.tipo = 9 then 'Lancha Voadeira'
                when  v.tipo = 10 then 'Barco de Madeira'
                when  v.tipo = 11 then 'Barco de Aluminio'
                when  v.tipo = 12 then 'Canoa Motorizada'
                when  v.tipo = 13 then 'Canoa a Remo'
                when  v.tipo = 14 then 'Bicicleta'
                when  v.tipo = 99 then 'Outro' end as categoria, count(*) as qtd from sete.sete_veiculos v where v.codigo_cidade = {$codigoCidade}
                group by 1";
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

    public function getMediaPassageirosPorVeiculo($codigoCidade) {
        $sql = "select (select count(*) as qtd_alunos from sete.sete_rota_possui_veiculo srpv
                inner join sete.sete_rota_atende_aluno rtal on srpv.id_rota = rtal.id_rota and srpv.codigo_cidade  = rtal.codigo_cidade 
                where srpv.codigo_cidade = {$codigoCidade}) as qtd_alunos,

                (select count(*) as qtd_alunos from sete.sete_veiculos vcl
                where vcl.codigo_cidade = {$codigoCidade}) as qtd_veiculo";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        if ($row['qtd_veiculo'] > 0) {
            return $row['qtd_alunos'] / $row['qtd_veiculo'];
        } else {
            return 0;
        }
    }

    public function getMediaCapacidadeDosVeiculos($codigoCidade) {
        $sql = "select avg(v.capacidade) as capacidade_media from sete.sete_veiculos v where v.codigo_cidade = {$codigoCidade}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['capacidade_media'];
    }
    
    public function getMediaIdadeDosVeiculos($codigoCidade) {
        $sql = "select avg(extract(year from now()) - v.ano) as media_idade  from sete.sete_veiculos v where v.codigo_cidade = {$codigoCidade}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['media_idade'];
    }
    
    public function getQuantidadeVeiculosPorMarca($codigoCidade) {
        $sql = "select mv.nm_marca , count(*) as qtd from sete.sete_veiculos v 
                inner join sete.glb_marca_veiculos mv on v.marca = mv.id_marca 
                where v.codigo_cidade = {$codigoCidade}
                group by 1";
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
    
    public function getQuantidadeVeiculosPorModelo($codigoCidade) {
        $sql = "select 
                case when v.modo = '0' then 'Não se aplica'
                when v.modo = '1' then 'ORE 1'
                when v.modo = '2' then 'ORE 1 (4x4)'
                when v.modo = '3' then 'ORE 2'
                when v.modo = '4' then 'ORE 3'
                when v.modo = '5' then 'ORE 4'
                when v.modo = '6' then 'ONUREA'
                when v.modo = '7' then 'Lancha a Gasolina'
                when v.modo = '8' then 'Lancha a Diesel' end as modelo, count(*) as qtd from sete.sete_veiculos v 
                where v.codigo_cidade = {$codigoCidade}
                 group by 1";
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
    
    public function getQuantidadeVeiculosPorOrigem($codigoCidade) {
        $sql = "select 
                case when v.origem = 1 then 'Próprio'
                when v.origem = 2 then 'Terceirizado' end as origem, count(*) as qtd from sete.sete_veiculos v 
                where v.codigo_cidade = {$codigoCidade}
                  group by 1";
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
    
    public function getQuantidadeRotasPorTipo($codigoCidade) {
        $sql = "select 
                case when sr.tipo = 1 then 'Rodoviária'
                when sr.tipo = 2 then 'Aquaviaria'
                when sr.tipo = 3 then 'Mista' end tipo, count(*) as qtd from sete.sete_rotas sr 
                where sr.codigo_cidade = {$codigoCidade}
                  group by 1";
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
    
    public function getDadosKilometragemRotas($codigoCidade) {
        $sql = "select min(km) as menor, avg(km) as media, max(km) as maior from sete.sete_rotas sr
                    where sr.codigo_cidade = {$codigoCidade}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row;
    }
    
    public function getDadosTempoRotas($codigoCidade) {
        $sql = "select min(tempo) as menor, avg(tempo) as media, max(tempo) as maior from sete.sete_rotas sr
                    where sr.codigo_cidade = {$codigoCidade}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row;
    }
    
    public function getDadosKilometragemTotalRotas($codigoCidade) {
        $sql = "select sum(km) as total from sete.sete_rotas sr
                    where sr.codigo_cidade = {$codigoCidade}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['total'];
    }
    
    public function getDadosTempoTotalRotas($codigoCidade) {
        $sql = "select sum(tempo) as total from sete.sete_rotas sr
                    where sr.codigo_cidade = {$codigoCidade}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['total'];
    }
    
    public function getQuantidadeRotasPorTurno($codigoCidade) {
        $sql = "select 
                'Manhã' as horario_funcionamento, count(*) as qtd from sete.sete_rotas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.turno_matutino  = 'S'
                union 
                select 
                'Tarde' as horario_funcionamento, count(*) as qtd from sete.sete_rotas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.turno_vespertino  = 'S'
                union 
                select 
                'Noite' as horario_funcionamento, count(*) as qtd from sete.sete_rotas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.turno_noturno  = 'S'";
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
    
    public function getQuantidadeRotasPorDificuldade($codigoCidade) {
        $sql = "select 
                'Porteira' as dificuldade, count(*) as qtd from sete.sete_rotas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.da_porteira  = 'S'
                union 
                select 
                'Mata-Burro' as dificuldade, count(*) as qtd from sete.sete_rotas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.da_mataburro  = 'S'
                union 
                select 
                'Colchete' as dificuldade, count(*) as qtd from sete.sete_rotas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.da_colchete  = 'S'
                union 
                select 
                'Atoleiro' as dificuldade, count(*) as qtd from sete.sete_rotas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.da_atoleiro  = 'S'
                union 
                select 
                'Ponte-Rústica' as dificuldade, count(*) as qtd from sete.sete_rotas sesc where sesc.codigo_cidade = {$codigoCidade}
                and sesc.da_ponterustica  = 'S'";
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

    public function escolaExisteByCodigoMEC($codigoCidade, $codigoEntidadeMec) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("mec_co_entidade = '{$codigoEntidadeMec}' AND codigo_cidade = '{$codigoCidade}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getLista($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['codigo_cidade', 'id_escola', 'nome', 'loc_latitude', 'loc_longitude', 'horario_matutino', 'horario_vespertino', 'horario_noturno', 'horario_integral', 'ensino_medio', 'ensino_fundamental', 'ensino_superior', 'ensino_pre_escola', 'mec_tp_localizacao'])
                ->where("codigo_cidade = {$municipio}");
        $arLista = [];
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function qtdEscolasAtendidas($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute();
        if ($row->count() > 0) {
            $row = $row->current();
            return $row['qtd'];
        } else {
            return 0;
        }
    }

    public function qtdAlunosPorEscola($municipio, $idEscola = null) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(new \Laminas\Db\Sql\TableIdentifier('sete_escola_tem_alunos', 'sete'))
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}");
        if (!empty($idEscola)) {
            $select->where("id_escola = {$idEscola}");
        }
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }

    public function qtdAlunosPorCidade($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(new \Laminas\Db\Sql\TableIdentifier('sete_escola_tem_alunos', 'sete'))
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("codigo_cidade = {$municipio}");
        if (!empty($idEscola)) {
            $select->where("id_escola = {$idEscola}");
        }
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['qtd'];
    }

    public function escolaExiste($idEscola, $codigoCidade) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("id_escola = '{$idEscola}' AND codigo_cidade = '{$codigoCidade}'");
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
                ->columns(['id' => new \Laminas\Db\Sql\Expression("max(id_escola)")]);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['id'];
    }

    public function _atualizar($arId, $dados) {
        $this->sql = new Sql($this->AdapterBD);
        $update = $this->sql->update($this->tableIdentifier);
        $update->set($dados);
        $update->where(["codigo_cidade" => $arId['codigo_cidade'], 'id_escola' => $arId['id_escola']]);
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
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $ex) {
            $bool = false;
            $message = "Falha ao atualizar o registro. " . $ex->getMessage();
        }
        return ['result' => $bool, 'messages' => $message];
    }

    public function _delete($arIds) {
        $this->sql = new Sql($this->AdapterBD);
        $delete = $this->sql->delete($this->tableIdentifier);
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND id_escola = {$arIds['id_escola']}");
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
