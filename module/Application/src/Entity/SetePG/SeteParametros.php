<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteParametros extends AbstractDatabasePostgres {
    
    const PERC_ENCARGO_SOCIAIS = 'PERC_ENCARGO_SOCIAIS';
    const PERC_CFT_CUSTO_MANUTENCAO_RODO = 'PERC_CFT_CUSTO_MANUTENCAO_RODO';
    const PERC_CFT_CUSTO_MANUTENCAO_AQUA = 'PERC_CFT_CUSTO_MANUTENCAO_AQUA';
    const VIDA_UTIL_RODO = 'VIDA_UTIL_RODO';
    const VIDA_UTIL_AQUA = 'VIDA_UTIL_AQUA';
    const PERC_RESIDUAL_RODO = 'PERC_RESIDUAL_RODO';
    const PERC_RESIDUAL_AQUA = 'PERC_RESIDUAL_AQUA';
    const PERC_TRC = 'PERC_TRC';
    const PRECO_MEDIO_COMBUSTIVEIS = 'PRECO_MEDIO_COMBUSTIVEIS';
    const CFT_CONSUMO_OLEO_LUBRIFICANTE = 'CFT_CONSUMO_OLEO_LUBRIFICANTE';
    const PRECO_MEDIO_PNEUS = 'PRECO_MEDIO_PNEUS';
    const PRECO_MEDIO_RECAPAGEM = 'PRECO_MEDIO_RECAPAGEM';
    const CFT_CONSUMO_PECAS = 'CFT_CONSUMO_PECAS';
    const NUM_RECAPAGEM = 'NUM_RECAPAGEM';
    const PERC_SEGURO_AQUA = 'PERC_SEGURO_AQUA';
    const DENSIDADE_COMBUSTIVEL = 'DENSIDADE_COMBUSTIVEL';
    const CONSUMO_LUBRIFICANTE = 'CONSUMO_LUBRIFICANTE';
    const DENSIDADE_LUBRIFICANTE = 'DENSIDADE_LUBRIFICANTE';
    const PRECO_MEDIO_LUBRIFICANTE = 'PRECO_MEDIO_LUBRIFICANTE';
    const PERC_MANUTENCAO_EMBARCACAO = 'PERC_MANUTENCAO_EMBARCACAO';
    const PRECO_MEDIO_GASOLINA = 'PRECO_MEDIO_GASOLINA';
    const PRECO_MEDIO_DIESEL = 'PRECO_MEDIO_DIESEL';
    const PRECO_MEDIO_ETANOL = 'PRECO_MEDIO_ETANOL';
    const PRECO_MEDIO_GAS_NATURAL = 'PRECO_MEDIO_GAS_NATURAL';
    const PRECO_MEDIO_OUTRO_COMBUSTIVEL = 'PRECO_MEDIO_OUTRO_COMBUSTIVEL';
    const CONSUMO_COMBUSTIVEL_AQUAVIARIO = 'CONSUMO_COMBUSTIVEL_AQUAVIARIO';

    const PARAMETROS = [
        ['codigo_parametro' => 'PERC_ENCARGO_SOCIAIS', 'valor_padrao' => 20, 'valor' => 20, 'descricao_parametro' => 'PERCENTUAL DOS ENCARGOS SOCIAIS SOB O SALÁRIOS DOS MOTORISTAS.'],
        ['codigo_parametro' => 'PERC_CFT_CUSTO_MANUTENCAO_RODO', 'valor_padrao' => 13.5, 'valor' => 13.5, 'descricao_parametro' => 'COEFICIENTE DE CUSTO DE MANUTENÇÃO (RODOVIÁRIO)'],
        ['codigo_parametro' => 'PERC_CFT_CUSTO_MANUTENCAO_AQUA', 'valor_padrao' => 13.5, 'valor' => 13.5, 'descricao_parametro' => 'COEFICIENTE DE CUSTO DE MANUTENÇÃO (AQUAVIÁRIO)'],
        ['codigo_parametro' => 'VIDA_UTIL_RODO', 'valor_padrao' => 10, 'valor' => 10, 'descricao_parametro' => 'VIDA ÚTIL DO VEÍCULO RODOVIÁRIO EM ANOS'],
        ['codigo_parametro' => 'VIDA_UTIL_AQUA', 'valor_padrao' => 10, 'valor' => 10, 'descricao_parametro' => 'VIDA ÚTIL DA EMBARCAÇÃO RODOVIÁRIO EM ANOS'],
        ['codigo_parametro' => 'PERC_RESIDUAL_RODO', 'valor_padrao' => 15, 'valor' => 15, 'descricao_parametro' => 'PERCENTUAL DO VALOR RESIDUAL DO VEÍCULO RODOVIÁRIO DE ACORDO COM O QUE RESTA DA VIDA ÚTIL.'],
        ['codigo_parametro' => 'PERC_RESIDUAL_AQUA', 'valor_padrao' => 15, 'valor' => 15, 'descricao_parametro' => 'PERCENTUAL DO VALOR RESIDUAL DO VEÍCULO AQUAVIARIO DE ACORDO COM O QUE RESTA DA VIDA ÚTIL.'],
        ['codigo_parametro' => 'PERC_TRC', 'valor_padrao' => 9.25, 'valor' => 9.25, 'descricao_parametro' => 'TAXA DE REMUNERAÇÃO DO CAPITAL'],
        ['codigo_parametro' => 'CFT_CONSUMO_OLEO_LUBRIFICANTE', 'valor_padrao' => 0.05, 'valor' => 0.05, 'descricao_parametro' => 'COEFICIENTE DE CONSUMO DE ÓLEOS E LUBRIFICANTES (L/KM)'],
        ['codigo_parametro' => 'NUM_RECAPAGEM', 'valor_padrao' => 3, 'valor' => 3, 'descricao_parametro' => 'NÚMERO DE RECAPAGENS QUE SÃO FEITAS NOS PNEUS DO VEÍCULOS'],
        ['codigo_parametro' => 'CFT_CONSUMO_PECAS', 'valor_padrao' => 0.0058, 'valor' => 0.0058, 'descricao_parametro' => 'COEFICIENTE DE CONSUMO DE PEÇAS E ACESSÓRIOS'],
        ['codigo_parametro' => 'PERC_SEGURO_AQUA', 'valor_padrao' => 4.2, 'valor' => 4.2, 'descricao_parametro' => 'TAXA DE SEGURO DAS EMBARCAÇÕES (4.2%)'],
        ['codigo_parametro' => 'PERC_MANUTENCAO_EMBARCACAO', 'valor_padrao' => 4, 'valor' => 4, 'descricao_parametro' => 'TAXA ANUAL DE MANUTENÇÃO E REPARO DA EMBARCAÇÃO-TIPO'],
        ['codigo_parametro' => 'DENSIDADE_COMBUSTIVEL', 'valor_padrao' => 0.85, 'valor' => 0.85, 'descricao_parametro' => 'DENSIDADE DE COMBUSTÍVEL (KG/LITRO)'],
        ['codigo_parametro' => 'CONSUMO_LUBRIFICANTE', 'valor_padrao' => 0.002, 'valor' => 0.002, 'descricao_parametro' => 'CONSUMO ESPECÍFICO DE LUBRIFICANTE (KG/HP)'],
        ['codigo_parametro' => 'DENSIDADE_LUBRIFICANTE', 'valor_padrao' => 0.9, 'valor' => 0.9, 'descricao_parametro' => 'DENSIDADE DO LUBRIFICANTE (KG/LITRO)'],
        ['codigo_parametro' => 'PRECO_MEDIO_LUBRIFICANTE', 'valor_padrao' => null, 'valor' => null, 'descricao_parametro' => 'PREÇO MÉDIO DO LUBRIFICANTE (R$ / LITRO);'],
        ['codigo_parametro' => 'PRECO_MEDIO_GASOLINA', 'valor_padrao' => null, 'valor' => null, 'descricao_parametro' => 'PREÇO MÉDIO DO LITRO DA GASOLINA'],
        ['codigo_parametro' => 'PRECO_MEDIO_DIESEL', 'valor_padrao' => null, 'valor' => null, 'descricao_parametro' => 'PREÇO MÉDIO DO LITRO DO DIESEL'],
        ['codigo_parametro' => 'PRECO_MEDIO_ETANOL', 'valor_padrao' => null, 'valor' => null, 'descricao_parametro' => 'PREÇO MÉDIO DO LITRO DO ETANOL'],
        ['codigo_parametro' => 'PRECO_MEDIO_GAS_NATURAL', 'valor_padrao' => null, 'valor' => null, 'descricao_parametro' => 'PREÇO MÉDIO DO KG DO GÁS NATURAL'],
        ['codigo_parametro' => 'PRECO_MEDIO_OUTRO_COMBUSTIVEL', 'valor_padrao' => null, 'valor' => null, 'descricao_parametro' => 'PREÇO MÉDIO OUTRO COMBUSTIVEL'],
        ['codigo_parametro' => 'PRECO_MEDIO_PNEUS', 'valor_padrao' => null, 'valor' => null, 'descricao_parametro' => 'PREÇO MÉDIO DOS PNEUS'],
        ['codigo_parametro' => 'PRECO_MEDIO_RECAPAGEM', 'valor_padrao' => null, 'valor' => null, 'descricao_parametro' => 'PREÇO MÉDIO DO SERVIÇO DE RECAPAGEM'],
        ['codigo_parametro' => 'CONSUMO_COMBUSTIVEL_AQUAVIARIO', 'valor_padrao' => 0.18, 'valor' => 0.18, 'descricao_parametro' => 'CONSUMOS ESPECIFICO PARA ROTAS AQUAVIARIAS']
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
                ->columns(['valor'])
                ->where("eta.codigo_cidade = {$arIds['codigo_cidade']} AND eta.codigo_parametro = '{$arIds['codigo_parametro']}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row['valor'];
    }
    
    public function getLista($codigoCidade){
        $sql = "select codigo_parametro, valor_padrao,  valor, descricao_parametro, descricao_detalhada from sete.sete_parametros 
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
    
    public function getParametros($codigoCidade){
        $arListaParametros = $this->getLista($codigoCidade);
        $arParametros = [];
        foreach ($arListaParametros as $row){
            $arParametros[$row['codigo_parametro']] = $row['valor'];
        }
        return $arParametros;        
    }

    public function startupParametros($codigoCidade) {
        foreach (self::PARAMETROS as $param) {
            $param['codigo_cidade'] = $codigoCidade;
            $this->_inserir($param);
        }
    }

    public function parametrosJaCriadosParaCidade($codigoCidade) {
        $sql = "select count(*) as qtd from sete.sete_parametros 
                    where codigo_cidade = '{$codigoCidade}'";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $row = $statement->execute()->current();
        if($row['qtd'] > 0){
            return true;
        }else{
            return false;
        }
    }
    
    public function _atualizar($arIds, $valor) {
        $this->sql = new Sql($this->AdapterBD);
        $update = $this->sql->update($this->tableIdentifier);
        $update->set(['valor' => $valor]);
        $update->where([$this->primaryKey => $arIds['codigo_parametro'], 'codigo_cidade' => $arIds['codigo_cidade']]);
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

}
