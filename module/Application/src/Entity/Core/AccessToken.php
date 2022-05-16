<?php

namespace Db\Core;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class AccessToken extends AbstractDatabasePostgres {

    const EXPIRES_ACCESS_TOKEN = 10800; //3 horas
    const TIPO_ADMINISTRATIVO = 'administrativo';

    public function __construct() {
        $this->table = 'api_access_token';
        $this->primaryKey = 'access_token';
        $this->schema = 'api';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function accessTokenValido($accessToken) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['dt_criacao', 'expires'])
                ->where("access_token = '{$accessToken}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arCount = $prepare->execute()->count();
        if ($arCount > 0) {
            $arRow = $prepare->execute()->current();
            $secondsCriacaoToken = strtotime($arRow['dt_criacao']);
            $secondsAtual = strtotime('now');
            $tempoPercorrido = $secondsAtual - $secondsCriacaoToken;
            if ($tempoPercorrido > $arRow['expires']) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function getEmailUsuarioByAccessToken($accessToken) {
        $sql = "SELECT email FROM api.api_access_token ac
                    INNER JOIN sete.usuarios us ON us.id = ac.id_usuario
                    WHERE ac.access_token = '{$accessToken}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        if ($statement->execute()->count() > 0) {
            $row = $statement->execute()->current();
            return $row['email'];
        } else {
            return null;
        }
    }

    public function getEmailUsuarioSETEByAccessToken($accessToken) {
        $sql = "SELECT email FROM api.api_access_token ac
                    INNER JOIN sete.sete_usuarios us ON us.id_usuario = ac.id_usuario
                    WHERE ac.access_token = '{$accessToken}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        if ($statement->execute()->count() > 0) {
            $row = $statement->execute()->current();
            return $row['email'];
        } else {
            return null;
        }
    }

    public function getNivelByAccessToken($accessToken) {
        $sql = "SELECT nivel_permissao FROM api.api_access_token ac
                    INNER JOIN sete.sete_usuarios us ON us.id_usuario = ac.id_usuario
                    WHERE ac.access_token = '{$accessToken}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        return $row['nivel_permissao'];
    }

    public function getAccessTokenByUsuario($idUsuario) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['access_token'])
                ->where("id_usuario = {$idUsuario}")
                ->order("dt_criacao DESC")
                ->limit(1);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arRow = $prepare->execute()->current();
        return $arRow['access_token'];
    }

    public function gerarAccessTokenAPI($usuario) {
        $dbSIRUsuario = new \Db\Core\Usuario();
        $idUsuario = $dbSIRUsuario->getIdUsuarioByUsername($usuario);
        $accessToken = sha1(time()) . "-{$usuario}";
        $dataCriacao = date("Y-m-d H:i:s");

        $this->_inserir([
            'access_token' => $accessToken,
            'id_usuario' => $idUsuario,
            'expires' => self::EXPIRES_ACCESS_TOKEN,
            'dt_criacao' => $dataCriacao,
            'tipo' => self::TIPO_ADMINISTRATIVO
        ]);

        return [
            'access_token' => $accessToken,
            'expires_in' => self::EXPIRES_ACCESS_TOKEN
        ];
    }

    public function gerarAccessTokenUsuarioSETE($usuario) {
        $dbSIRUsuario = new \Db\SetePG\SeteUsuarios();
        $idUsuario = $dbSIRUsuario->getIdUsuarioByUsername($usuario);
        $arUsuario = $dbSIRUsuario->getUsuarioByUsername($usuario);
        $accessToken = sha1(time()) . "-{$usuario}";
        $dataCriacao = date("Y-m-d H:i:s");

        $row = $this->_inserir([
            'access_token' => $accessToken,
            'id_usuario' => $idUsuario,
            'expires' => self::EXPIRES_ACCESS_TOKEN,
            'dt_criacao' => $dataCriacao,
            'codigo_cidade' => $arUsuario['codigo_cidade']
        ]);

        return [
            'access_token' => $accessToken,
            'expires_in' => self::EXPIRES_ACCESS_TOKEN
        ];
    }

    public function getCodigoCidadeUsuarioAutenticado($accessToken) {
        $sql = "select us.codigo_cidade from api.api_access_token aat 
                    inner join sete.sete_usuarios us on aat.id_usuario = us.id_usuario 
                    where aat.access_token = '{$accessToken}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        if ($statement->execute()->count() > 0) {
            $row = $statement->execute()->current();
            return $row['codigo_cidade'];
        } else {
            return null;
        }
    }
    
    public function getTipoAccessToken($accessToken){
        $sql = "select tipo from api.api_access_token aat  where "
                . "aat.access_token = '{$accessToken}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        if ($statement->execute()->count() > 0) {
            $row = $statement->execute()->current();
            return $row['tipo'];
        } else {
            return null;
        }
    }

}
