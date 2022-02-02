<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteUsuarios extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_usuarios';
        $this->primaryKey = 'id_usuario';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($idUsuario, $codigoCidade) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$codigoCidade} AND id_usuario = {$idUsuario}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }

    public function getLista($municipio) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$municipio}");
        $arLista = [];
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function usuarioExiste($cpf) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("cpf = '{$cpf}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function usuarioExisteById($idUsuario, $codigoCidade) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("id_usuario = '{$idUsuario}' AND codigo_cidade = {$codigoCidade}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        if ($row['qtd'] > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function usuarioExisteByEmail($email, $codigoCidade = null) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['qtd' => new \Laminas\Db\Sql\Expression("count(*)")])
                ->where("email = '{$email}'");
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
                ->columns(['id' => new \Laminas\Db\Sql\Expression("max(id_usuario)")]);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['id'];
    }

    public function _atualizar($arId, $dados) {
        $this->sql = new Sql($this->AdapterBD);
        $update = $this->sql->update($this->tableIdentifier);
        $update->set($dados);
        $update->where(['codigo_cidade' => $arId['codigo_cidade'], 'id_usuario' => $arId['id_usuario']]);
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
        $delete->where("codigo_cidade =  '{$arIds['codigo_cidade']}' AND id_usuario = {$arIds['id_usuario']}");
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
    
    public function checkUsuarioAndPassword($usuario, $pass) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->columns(['email', 'password'])
                ->where("email = '{$usuario}'")
                ->where("password = '{$pass}'")
                ->where("is_ativo = 'S'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        if ($execute->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getUsuarioByAccessToken($accessToken){
        $sql = "select su.id_usuario, su.nome, su.nivel_permissao as tipo_permissao, su.codigo_cidade, su.cidade, su.estado, 
                    su.cpf, su.telefone, su.email, su.foto
                    from api.api_access_token aat
                    inner join sete.sete_usuarios su on su.id_usuario = aat.id_usuario and aat.codigo_cidade = su.codigo_cidade 
                    where aat.access_token  = '{$accessToken}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row;
    }
    
    public function getUsuarioByUsername($username){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("email = '{$username}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row;
    }
    
    public function getIdUsuarioByUsername($usuario){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_usuario'])
                ->where("email = '{$usuario}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row['id_usuario'];
    }

}
