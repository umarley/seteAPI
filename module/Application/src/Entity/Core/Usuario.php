<?php

namespace Db\Core;

use Application\Utils\Ldap;
use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Adapter\Adapter;

class Usuario extends AbstractDatabasePostgres {

    const SITUACAO_ATIVO = 'A';
    const SITUACAO_PENDENTE = 'P';
    const SITUACAO_INATIVO = 'I';

    public function __construct() {
        $this->table = 'usuarios';
        $this->primaryKey = 'id';
        $this->schema = 'api';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getTotalUsuarios($busca = "") {
        $sql = "SELECT count(*) as qtd FROM api.usuarios us";
        if (!empty($busca)) {
            $sql .= " WHERE (us.nome LIKE '%{$busca}%' OR us.email LIKE '%{$busca}%')";
        }
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['qtd'];
    }

    public function getLista($offset, $limit = 20, $busca = "") {
        $sql = "SELECT id, nome, email, is_ativo FROM api.usuarios us";
        if (!empty($busca)) {
            $sql .= " WHERE (us.nome LIKE '%{$busca}%' OR us.email LIKE '%{$busca}%')";
        }
        $sql .= " OFFSET {$offset} LIMIT {$limit}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $this->getResultSet($statement->execute());
        foreach ($this->resultSet as $row) {
            $arLista[] = $row;
        }
        return $arLista;
    }

    public function getNomeUsuarioByUsername($username) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['nm_usuario'])
                ->where("usuario = '{$username}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row['nm_usuario'];
    }

    /* public function getUsuarioById($id){
      $sql = new Sql($this->AdapterBD);
      $select = $sql->select(['us' => $this->tableIdentifier])
      ->columns(['*'])
      ->join(['ps' => new TableIdentifier('glb_pessoa')], "ps.id_pessoa = us.id_pessoa", ['segmento' => 'id_segmento_cadastro', 'nm_pessoa', 'situacao'])
      ->where("id_usuario = '{$id}'");
      $prepare = $sql->prepareStatementForSqlObject($select);
      $execute = $prepare->execute();
      $row = $execute->current();
      return $row;
      } */

    public function getUsuarioByUsername($username) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("email = '{$username}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row;
    }

    public function getNomeUsuarioByEmail($email) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['nome'])
                ->where("email = '{$email}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row['nome'];
    }

    public function getIdUsuarioByUsername($usuario) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id'])
                ->where("email = '{$usuario}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row['id'];
    }

    /* public function usuarioIsTrocarSenha($usuario){
      $sql = new Sql($this->AdapterBD);
      $select = $sql->select($this->tableIdentifier)
      ->columns(['trocar_senha'])
      ->where("usuario = '{$usuario}'");
      $prepare = $sql->prepareStatementForSqlObject($select);
      $execute = $prepare->execute();
      $row = $execute->current();
      if($row['trocar_senha'] === \Db\SIR\Config::SIM){
      return true;
      }else{
      return false;
      }
      } */

    public static function trataNomeParaExibirSistema($nomeUsuario) {
        $parts = explode(" ", $nomeUsuario);
        $nomeExibir = $parts[0];
        if (isset($parts[1]) && !empty($parts[1])) {
            $nomeExibir .= " {$parts[1]}";
        }
        if (isset($parts[2]) && !empty($parts[2]) && strlen($parts[1]) <= 2) {
            $nomeExibir .= " {$parts[2]}";
        }
        return $nomeExibir;
    }

    public function usuarioExiste($email) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id'])
                ->where("email = '{$email}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        if ($execute->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getIdUsuarioByEmail($email) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id'])
                ->where("email = '{$email}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        if ($row) {
            return $row['id'];
        }
        return false;
    }

    private function limparTexto($texto) {
        return str_replace(["<", ">", "=", "'", "?"], "", $texto);
    }

    public function checkUsuarioAndPassword($usuario, $pass) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->columns(['email', 'senha'])
                ->where("email = '" . $this->limparTexto($usuario) . "'")
                ->where("senha = '" . $this->limparTexto($pass) . "'")
                ->where("is_ativo = 'S'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        if ($execute->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function find($username) {
        $container = new \Zend\Session\Container('Auth');
        $arAcesso['username'] = $container->usuario;
        $arAcesso['password'] = base64_decode($container->token);
        $ldap = new Ldap($arAcesso);
        $arLista = $ldap->getListaUsuarios($ldap::FILTRO_POR_USUARIO, $username);
        return $arLista;
    }

}
