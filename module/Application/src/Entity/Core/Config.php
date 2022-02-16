<?php

namespace Db\Core;


class Config extends AbstractDatabasePostgres{
   
    const SIM = 'S';
    const NAO = 'N';
    const SESSION_AUTH_ADM = 'Auth';
    const SESSION_AUTH_PUB = 'AuthPub';
    const SERVER_SMTP = 'SERVER_SMTP';
    const USER_SMTP = 'USER_SMTP';
    const SENHA_SMTP = 'SENHA_SMTP';
    const PORTA_SMTP = 'PORTA_SMTP';
    
    
    public function __construct() {
        $this->table = 'config';
        $this->primaryKey = 'variable';
        $this->schema = 'sistema';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }
    
    public function getListaByVariable($sParametro){
        $sql = "SELECT * FROM sys_config WHERE variable LIKE '%{$sParametro}%'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute();
        $arParametros = [];
        foreach ($result as $row){
            $arParametros[] = $row;
        }
        return $arParametros;
    }
    
    public function getById($variable){
        $sql = "SELECT * FROM {$this->schema}.{$this->table} WHERE {$this->primaryKey} = '{$variable}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(); 
        return $result->current();
    }
    
    public function setConfig($variable, $value){
        return $this->_atualizar($variable, ['value' => $value]);
    }
    
    public function getConfig($variable, $idModulo = '')
    {
        $sql = "SELECT value FROM {$this->schema}.{$this->table} WHERE {$this->primaryKey} = '{$variable}'";
        if(!empty($idModulo)){
            $sql .= " AND modulo_id = {$idModulo}";
        }
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $result = $statement->execute(); 
        if($result->count() > 0){
            $row = $result->current();
            return $row['value'];
        }else{
            throw new \Exception("Parâmetro {$variable} não encontrado!", 1125);
        }

    }

    public function getArrayMultiplosValores($valorConfiguracao){
        $valorPuro = str_replace(['[', ']'], '', $valorConfiguracao);
        $arrayValores = array_map('trim', explode(",", $valorPuro));
        return $arrayValores;
    }

    public static function getLabelSimNao($value){
        switch ($value){
            case self::SIM:
                return "Sim";
                break;
            case self::NAO:
                return "Não";
                break;
        }
    }
    
}
