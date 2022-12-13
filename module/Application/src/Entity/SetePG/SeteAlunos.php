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
    
    public function alunoExisteById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND id_aluno = {$arIds['id_aluno']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->count();
        if($row > 0){
            return true;
        }else{
            return false;
        }
    }

    public function getLista($municipio) {
        $sql = "select a.codigo_cidade, a.id_aluno, a.nome, cpf, a.loc_latitude, a.loc_longitude, a.nivel, a.turno, a.mec_tp_localizacao,
                    coalesce(esc.nome, 'Não Informada') as escola,
                    case when (select count(*) from sete.sete_rota_atende_aluno sraa where sraa.codigo_cidade = a.codigo_cidade and a.id_aluno = sraa.id_aluno) > 1 then 'Sim' else 'Não' end as rota
                    from sete.sete_alunos a 
                    left join sete.sete_escola_tem_alunos eta on a.id_aluno = eta.id_aluno and a.codigo_cidade  = eta.codigo_cidade
                    left join sete.sete_escolas esc on esc.id_escola  = eta.id_escola and esc.codigo_cidade  = eta.codigo_cidade  
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
        if ($idAluno != "") {
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

    public function qtdAlunosEscolaridade($codigoCidade) {
        $sql = "SELECT 1 AS nivel,
                    'Infantil' AS nm_nivel,
                    (select count(*) from sete.sete_alunos al where al.codigo_cidade = '{$codigoCidade}' and al.nivel = 1) as qtd
            UNION ALL SELECT 2 AS nivel,
                            'Fundamental' AS nm_nivel,
                            (select count(*) from sete.sete_alunos al where al.codigo_cidade = '{$codigoCidade}' and al.nivel = 2) as qtd
            UNION ALL SELECT 3 AS nivel,
                            'Médio' AS nm_nivel,
                            (select count(*) from sete.sete_alunos al where al.codigo_cidade = '{$codigoCidade}' and al.nivel = 3) as qtd
            UNION ALL SELECT 4 AS nivel,
                            'Superior' AS nm_nivel,
                            (select count(*) from sete.sete_alunos al where al.codigo_cidade = '{$codigoCidade}' and al.nivel = 4) as qtd
            UNION ALL SELECT 5 AS nivel,
                            'Outro' AS nm_nivel,
                            (select count(*) from sete.sete_alunos al where al.codigo_cidade = '{$codigoCidade}' and al.nivel = 5) as qtd";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function qtdAlunosTurno($codigoCidade) {
        $sql = "select 1 as tp_turno,
                'Manhã' as nm_tp_turno,
                (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and turno = 1) as qtd
                union all 
                select 2 as tp_turno,
                'Tarde' as nm_tp_turno,
                (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and turno = 2) as qtd
                union all 
                select 3 as tp_turno,
                'Integral' as nm_tp_turno,
                (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and turno = 3) as qtd
                union all 
                select 4 as tp_turno,
                'Noturno' as nm_tp_turno,
                (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and turno = 4) as qtd";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function qtdAlunosResidencia($codigoCidade) {
        $sql = "select 1 as tp_residencia,
                'Área Urbana' as nm_tp_residencia,
                (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and mec_tp_localizacao = 1) as qtd
                union all 
                select 2 as tp_residencia,
                'Área Rural' as nm_tp_residencia,
                (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and mec_tp_localizacao = 2) as qtd";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function qtdAlunosCor($codigoCidade) {
        $sql = "select '0' as tp_cor,
        'Não informado' as nm_tp_cor,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and cor = '0') as qtd
          union all
        select '1' as tp_cor,
        'Branco' as nm_tp_cor,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and cor = '1') as qtd
        union all 
        select '2' as tp_cor,
        'Preto' as nm_tp_cor,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and cor = '2') as qtd
        union all 
        select '3' as tp_cor,
        'Pardo' as nm_tp_cor,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and cor = '3') as qtd
        union all 
        select '4' as tp_cor,
        'Amarelo' as nm_tp_cor,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and cor = '4') as qtd
        union all 
        select '5' as tp_cor,
        'Indígena' as nm_tp_cor,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and cor =' 5') as qtd
        union all
        select 'NAN' as tp_cor,
        'Outra' as nm_tp_cor,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and cor = 'NAN') as qtd";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function qtdAlunosSexo($codigoCidade) {
        $sql = "select '1' as tp_sexo,
        'Masculino' as nm_tp_sexo,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and sexo = '1') as qtd
          union all
        select '2' as tp_sexo,
        'Feminino' as nm_tp_sexo,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and sexo = '2') as qtd
        union all 
        select '3' as tp_sexo,
        'Não Informado' as nm_tp_sexo,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and sexo = '3') as qtd";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function qtdAlunosResponsavel($codigoCidade) {
        $sql = "select '-1' as tp_responsavel,
        'Não informado' as nm_tp_responsavel,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and grau_responsavel = '-1') as qtd
          union all
        select '0' as tp_responsavel,
        'Pai, Mãe, Padrasto ou Madrasta ' as nm_tp_responsavel,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and grau_responsavel = '0') as qtd
        union all 
        select '1' as tp_responsavel,
        'Avô ou Avó' as nm_tp_responsavel,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and grau_responsavel = '1') as qtd
        union all 
        select '2' as tp_responsavel,
        'Irmão ou Irmã' as nm_tp_responsavel,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and grau_responsavel = '2') as qtd
        union all 
        select '4' as tp_responsavel,
        'Outro parente' as nm_tp_responsavel,
        (select count(*) from sete.sete_alunos al where al.codigo_cidade  = '{$codigoCidade}' and grau_responsavel = '4') as qtd";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }
    /**
     * Método utilizado na importação do censo
     */
    public function alunoExistePorChaveComposta($chave) {
        $sql = "select count(*) as qtd
                from sete.sete_alunos a
                where  replace(ltrim(rtrim(nome)) , ' ', '-') || '-' || data_nascimento = '{$chave}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        if($row['qtd'] > 0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Método utilizado na importação do censo
     */
    public function getAlunoPorChave($chave){
        $sql = "select *
                from sete.sete_alunos a
                where  replace(ltrim(rtrim(nome)) , ' ', '-') || '-' || data_nascimento = '{$chave}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row;
    }

    public function getByCPF($cpf) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("cpf = '{$cpf}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function alunoExistePUT($cpf, $idAluno = null) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("cpf = '{$cpf}' AND id_aluno <> {$idAluno}");
        $sqlBuild = $sql->buildSqlString($select);
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
        
        //echo $sql . "<br />";
        
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
