<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteParametros extends AbstractDatabasePostgres {

    const PARAMETROS = [
        ['codigo_parametro' => 'PERC_ENCARGO_SOCIAIS', 'valor' => 20, 'descricao_parametro' => 'Percentual dos encargos sociais sob o salários dos motoristas.'],
        ['codigo_parametro' => 'PERC_CFT_CUSTO_MANUTENCAO_RODO', 'valor' => 13.5, 'descricao_parametro' => 'Coeficiente de custo de manutenção (RODOVIÁRIO)'],
        ['codigo_parametro' => 'PERC_CFT_CUSTO_MANUTENCAO_AQUA', 'valor' => 13.5, 'descricao_parametro' => 'Coeficiente de custo de manutenção (AQUAVIÁRIO)'],
        ['codigo_parametro' => 'VIDA_UTIL_RODO', 'valor' => 10, 'descricao_parametro' => 'Vida útil do veículo rodoviário em anos'],
        ['codigo_parametro' => 'VIDA_UTIL_AQUA', 'valor' => 10, 'descricao_parametro' => 'Vida útil da embarcação rodoviário em anos'],
        ['codigo_parametro' => 'PERC_RESIDUAL_RODO', 'valor' => 15, 'descricao_parametro' => 'Percentual do valor residual do veículo rodoviário de acordo com o que resta da vida útil.'],
        ['codigo_parametro' => 'PERC_RESIDUAL_AQUA', 'valor' => 15, 'descricao_parametro' => 'Percentual do valor residual do veículo aquaviario de acordo com o que resta da vida útil.'],
        ['codigo_parametro' => 'PERC_TRC', 'valor' => 9.25, 'descricao_parametro' => 'Taxa de remuneração do capital'],
        ['codigo_parametro' => 'CFT_CONSUMO_OLEO_LUBRIFICANTE', 'valor' => 005, 'descricao_parametro' => 'COEFICIENTE DE CONSUMO DE ÓLEOS E LUBRIFICANTES (l/km)'],
        ['codigo_parametro' => 'NUM_RECAPAGEM', 'valor' => 3, 'descricao_parametro' => 'Número de recapagens que são feitas nos pneus do veículos'],
        ['codigo_parametro' => 'CFT_CONSUMO_PECAS', 'valor' => 0.0058, 'descricao_parametro' => 'COEFICIENTE DE CONSUMO DE PEÇAS E ACESSÓRIOS'],
        ['codigo_parametro' => 'PERC_SEGURO_AQUA', 'valor' => 4.2, 'descricao_parametro' => 'TAXA DE SEGURO DAS EMBARCAÇÕES (4.2%)'],
        ['codigo_parametro' => 'PERC_MANUTENCAO_EMBARCACAO', 'valor' => null, 'descricao_parametro' => ' TAXA ANUAL DE MANUTENÇÃO E REPARO DA EMBARCAÇÃO-TIPO'],
        ['codigo_parametro' => 'DENSIDADE_COMBUSTIVEL', 'valor' => 0.85, 'descricao_parametro' => 'DENSIDADE DE COMBUSTÍVEL (kg/litro)'],
        ['codigo_parametro' => 'CONSUMO_LUBRIFICANTE', 'valor' => 0.9, 'descricao_parametro' => 'CONSUMO ESPECÍFICO DE LUBRIFICANTE (kg/hp)'],
        ['codigo_parametro' => 'DENSIDADE_LUBRIFICANTE', 'valor' => 0.9, 'descricao_parametro' => 'DENSIDADE DO LUBRIFICANTE (kg/litro)'],
        ['codigo_parametro' => 'PRECO_MEDIO_LUBRIFICANTE', 'valor' => null, 'descricao_parametro' => 'Preço médio do lubrificante (R$ / litro);'],
        ['codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => null, 'descricao_parametro' => 'Preço médio do litro do combustível (diesel, gasolina, óleo e gás natural)']
    ];

    public function __construct() {
        $this->table = 'sete_parametros';
        $this->primaryKey = 'codigo_parametro';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['eta' => $this->tableIdentifier])
                ->join(['esc' => new \Laminas\Db\Sql\TableIdentifier('sete_escolas', 'sete')], "eta.id_escola = esc.id_escola AND eta.codigo_cidade = esc.codigo_cidade", ['*'])
                ->where("eta.codigo_cidade = {$arIds['codigo_cidade']} AND eta.id_aluno = {$arIds['id_aluno']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }
    
    public function getLista($codigoCidade){
        $sql = "select codigo_parametro, valor, descricao_parametro from sete_parametros 
                where codigo_cidade = {$codigoCidade}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $this->getResultSet($statement->execute());
        $arLista = [];
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;                
    }

    public function startupParametros($codigoCidade) {
        foreach (self::PARAMETROS as $param) {
            $param['codigo_cidade'] = $codigoCidade;
            $this->_inserir($param);
        }
    }

    public function parametrosJaCriadosParaCidade($codigoCidade) {
        $sql = "select count(*) as qtd from sete_parametros 
                    where codigo_cidade = {$codigoCidade}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        if($row['qtd'] > 0){
            return true;
        }else{
            return false;
        }
    }

}
