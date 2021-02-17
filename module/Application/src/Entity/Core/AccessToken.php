<?php

namespace Db\Core;

use Db\Core\AbstractDatabase;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate\Expression;

class AccessToken extends AbstractDatabase {

    const EXPIRES_ACCESS_TOKEN = 10800; //3 horas

    public function __construct() {
        $this->table = 'access_token';
        $this->primaryKey = 'access_token';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }

    public function accessTokenValido($accessToken) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['tempo_percorrido' => new Expression("unix_timestamp(NOW()) - unix_timestamp(dt_criacao)"), 'expires'])
                ->where("access_token = '{$accessToken}'");
        //echo $sql->buildSqlString($select);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $arCount = $prepare->execute()->count();
        if ($arCount > 0) {
            $arRow = $prepare->execute()->current();
            if ($arRow['tempo_percorrido'] > $arRow['expires']) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
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

    public function gerarAccessToken($usuario) {
        $dbSIRUsuario = new \Db\Core\Usuario();
        $idUsuario = $dbSIRUsuario->getIdUsuarioByUsername($usuario);
        $accessToken = sha1(time()) . "-{$usuario}";
        $dataCriacao = date("Y-m-d H:i:s");

        $this->_inserir([
            'access_token' => $accessToken,
            'id_usuario' => $idUsuario,
            'expires' => self::EXPIRES_ACCESS_TOKEN,
            'dt_criacao' => $dataCriacao
        ]);

        return [
            'access_token' => $accessToken,
            'expires_in' => self::EXPIRES_ACCESS_TOKEN
        ];
    }

}
