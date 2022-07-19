<?php

namespace Db\Core;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\ResultSet;

class AbstractDatabasePostgres extends TableGateway {

    const NUMERO_MAXIMO_REGISTRO = 1000;
    const DATABASE_CORE = 'sete_api';

    protected $AdapterBD;
    protected $primaryKey;
    protected $schema;
    protected $sql;
    protected $tableIdentifier;
    protected $configName;
    protected $resultSet;
    protected $dbConfig;

    /**
     * 
     * @param type $configName - Nome do array que contém a configuração de conexão com o banco de dados
     */
    public function __construct($configName) {
        if (is_null($this->dbConfig)) {
            $this->dbConfig = $this->montaConfiguracaoAdapter($configName);
        }
        $this->AdapterBD = new Adapter($this->dbConfig);
        $this->tableIdentifier = new TableIdentifier($this->table, $this->schema);
        parent::__construct($this->table, $this->AdapterBD);
    }

    public function getAdapter() {
        return $this->AdapterBD;
    }

    public function setTable($table) {
        $this->table = $table;
    }

    public function beginTransaction() {
        $this->AdapterBD->getDriver()->getConnection()->beginTransaction();
    }

    public function commit() {
        $this->AdapterBD->getDriver()->getConnection()->commit();
    }

    public function rollback() {
        $this->AdapterBD->getDriver()->getConnection()->rollback();
    }

    public function closeConnection() {
        $this->AdapterBD->getDriver()->getConnection()->disconnect();
    }

    protected function getResultSet($result) {
        $this->resultSet = new ResultSet\ResultSet();
        $this->resultSet->initialize($result);
    }

    public function _inserir(Array $data = []) {
        $this->sql = new Sql($this->AdapterBD);
        $insert = $this->sql->insert($this->tableIdentifier);
        $arColunas = [];
        $arValues = [];
        foreach ($data as $coluna => $value) {
            $arColunas[] = $coluna;
            $arValues[] = $value;
        }
        $insert->columns($arColunas);
        $insert->values($arValues);
        $sql = $this->sql->buildSqlString($insert);
        /* echo "=====<br />";
          echo $sql;
          echo "===========<br />"; */
        //$sql = str_replace("`", "", $sql);
        $statement = $this->AdapterBD->createStatement($sql);
        try {
            $result = $statement->execute();
            $bool = true;
            $message = ['id' => $result->getGeneratedValue()];
        } catch (\PDOException $ex) {
            
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $ex) {
            $message = "Falha ao inserir registro. Contacte o administrador do sistema! " . $ex->getMessage();
            //$this->rollback();
            $bool = false;
        }
        return ['result' => $bool, 'messages' => $message];
    }

    public function _deleteAll() {
        $sql = "DELETE FROM {$this->table}";
        try {
            $this->AdapterBD->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $boResultado = true;
            $message = "Dados excluidos com sucesso!";
        } catch (\PDOException $zAdapterEx) {
            $boResultado = false;
            $message = "Falha ao excluir registros tabela {$this->table}. <br />" . $zAdapterEx->getMessage();
        } catch (\Laminas\Db\Adapter\Exception\InvalidQueryException $zendDbExc) {
            $boResultado = false;
            $message = "Falha ao excluir registros tabela {$this->table}. Contacte o administrador do sistema para maiores informações. <br />" . $zendDbExc->getMessage();
        }
        return ['result' => $boResultado, 'messages' => $message];
    }

    public function _truncate() {
        $sql = "TRUNCATE TABLE {$this->table}";
        try {
            $this->AdapterBD->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $boResultado = true;
            $message = "Tabela truncada com sucesso!";
        } catch (\PDOException $zAdapterEx) {
            $boResultado = false;
            $message = "Falha ao truncar tabela {$this->table}. <br />" . $zAdapterEx->getMessage();
        } catch (\Laminas\Db\Adapter\Exception\InvalidQueryException $zendDbExc) {
            $boResultado = false;
            $message = "Falha ao trucar tabela {$this->table}. Contacte o administrador do sistema para maiores informações. <br />" . $zendDbExc->getMessage();
        }
        return ['result' => $boResultado, 'messages' => $message];
    }

    public function _atualizar($id, $dados) {
        $this->sql = new Sql($this->AdapterBD);
        $update = $this->sql->update($this->tableIdentifier);
        $update->set($dados);
        $update->where([$this->primaryKey => $id]);
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

    public function _delete($id) {
        $this->sql = new Sql($this->AdapterBD);
        $delete = $this->sql->delete($this->tableIdentifier);
        $delete->where($this->primaryKey . " =  '{$id}'");
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

    protected function montaSQLFiltro($sql, $param, $options = []) {
        $cont = 0;
        $like = false;
        if (key_exists('like', $options)) {
            $like = true;
        }

        foreach ($param as $key => $value) {
            if ($value !== "") {
                if ($cont === 0) {
                    if ($like && in_array($key, $options['like'])) {
                        $sql .= " WHERE " . str_replace(["|", "-"], ".", $key) . " LIKE '%{$param[$key]}%'";
                    } else {
                        $sql .= " WHERE " . str_replace(["|", "-"], ".", $key) . " = '{$param[$key]}'";
                    }
                } else {
                    if ($like && in_array($key, $options['like'])) {
                        $sql .= " AND " . str_replace(["|", "-"], ".", $key) . " LIKE '%{$param[$key]}%'";
                    } else {
                        $sql .= " AND " . str_replace(["|", "-"], ".", $key) . " = '{$param[$key]}'";
                    }
                }
                $cont++;
            }
        }
        //$sql .= " LIMIT " . self::NUMERO_MAXIMO_REGISTRO;
        return $sql;
    }

    private function montaConfiguracaoAdapter($configName) {
        /* if (AMBIENTE_EXEC === AMBIENTE_PRODUCAO) {
          $config = new \Laminas\Config\Config(include realpath('config/autoload/global.php'));
          } */
        $config = new \Laminas\Config\Config(include realpath('config/autoload/local.php'));

        $arConfig = [];
        foreach ($config->db->adapters->$configName as $key => $value) {
            $arConfig[$key] = $value;
        }
        return $arConfig;
    }

}
