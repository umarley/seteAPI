<?php

namespace Db\SetePG;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class SeteValidacaoDadosCusto extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'sete_parametros';
        $this->primaryKey = 'codigo_parametro';
        $this->schema = 'sete';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    private function getMotoristasDaRota($idRota, $codigoCidade){
        $sql = "select mt.cpf, mt.salario from sete.sete_rota_dirigida_por_motorista sr 
                    inner join sete.sete_motoristas mt on mt.codigo_cidade  = sr.codigo_cidade and sr.cpf_motorista = mt.cpf 
                    where sr.codigo_cidade = {$codigoCidade} and sr.id_rota = {$idRota}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $execute = $statement->execute();
        $this->getResultSet($execute);
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    private function getMonitoresDaRota($idRota, $codigoCidade){
        $sql = "select mnt.cpf, mnt.salario  from sete.sete_rota_atendida_por_monitor ram
                inner join sete.sete_monitores mnt on mnt.codigo_cidade = ram.codigo_cidade and ram.cpf_monitor = mnt.cpf 
                where ram.codigo_cidade = {$codigoCidade} and id_rota = {$idRota}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $execute = $statement->execute();
        $this->getResultSet($execute);
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    private function getVeiculosFrotaAquaviaria($idRota, $codigoCidade){
        $sql = "select v.placa, v.id_veiculo, v.potencia_do_motor from sete.sete_rota_possui_veiculo rpv 
                inner join sete.sete_veiculos v on v.codigo_cidade = rpv.codigo_cidade and v.id_veiculo = rpv.id_veiculo 
                where rpv.codigo_cidade = {$codigoCidade}  and rpv.id_rota = {$idRota}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $execute = $statement->execute();
        $this->getResultSet($execute);
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    private function getVeiculosFrotaGeral($idRota, $codigoCidade){
        $sql = "select v.placa, v.id_veiculo, v.seguro_anual, v.preco, v.ano, v.tipo_combustivel from sete.sete_rota_possui_veiculo rpv 
                inner join sete.sete_veiculos v on v.codigo_cidade = rpv.codigo_cidade and v.id_veiculo = rpv.id_veiculo 
                where rpv.codigo_cidade = {$codigoCidade}  and rpv.id_rota = {$idRota}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $execute = $statement->execute();
        $this->getResultSet($execute);
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    private function getVeiculosFrotaRodoviaria($idRota, $codigoCidade){
        $sql = "select v.placa, v.id_veiculo, v.ipva, v.dpvat, v.numero_de_pneus, v.consumo, v.vida_util_do_pneu from sete.sete_rota_possui_veiculo rpv 
                inner join sete.sete_veiculos v on v.codigo_cidade = rpv.codigo_cidade and v.id_veiculo = rpv.id_veiculo 
                where rpv.codigo_cidade = {$codigoCidade}  and rpv.id_rota = {$idRota}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $arLista = [];
        $execute = $statement->execute();
        $this->getResultSet($execute);
        foreach ($this->resultSet as $row){
            $arLista[] = $row;
        }
        return $arLista;
    }
    
    private function getQtdMotoristas($idRota, $codigoCidade){
        $sql = "select count(*) as qtd from sete.sete_rota_dirigida_por_motorista srm
                    where srm.codigo_cidade = {$codigoCidade} and srm.id_rota = {$idRota}";        
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $execute = $statement->execute()->current();
        return $execute['qtd'];
    }
    
    private function getQtdVeiculos($idRota, $codigoCidade){
        $sql = "select count(*) as qtd from sete.sete_rota_possui_veiculo srm
                    where srm.codigo_cidade = {$codigoCidade} and srm.id_rota = {$idRota}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $execute = $statement->execute()->current();
        return $execute['qtd'];
    }
    
    private function getQtdAlunos($idRota, $codigoCidade){
        $sql = "select count(*) as qtd from sete.sete_rota_atende_aluno srm
                    where srm.codigo_cidade = {$codigoCidade} and srm.id_rota = {$idRota}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $execute = $statement->execute()->current();
        return $execute['qtd'];
    }
    
    private function getQtdMonitores($idRota, $codigoCidade){
        $sql = "select COUNT(*) as qtd from sete.sete_rota_atendida_por_monitor ram
                where ram.codigo_cidade = {$codigoCidade} and id_rota = {$idRota}";
        $statement = $this->AdapterBD->createStatement($sql);
        $statement->prepare();
        $execute = $statement->execute()->current();
        return $execute['qtd'];
    }
    
    
    public function processarSalarioMotorista($idRota, $codigoCidade){
        $arMotoristas = $this->getMotoristasDaRota($idRota, $codigoCidade);
        $count = 0;
        $salario = 0;
        $cpfsInvalidos = [];
        $boValidate = true;
        foreach ($arMotoristas as $rowMotora){
            if($rowMotora->salario == "" || empty($rowMotora->salario)){
                $boValidate = false;
                $rowMotora->salario = 0;
                $cpfsInvalidos[] = $rowMotora->cpf;
            }
            $salario += $rowMotora->salario;
            $count++;
        }
        
        if($boValidate){
            if($count > 0){
                $valor = (float) $salario / $count;
            }else{
                $valor = (float) $salario;
            }
            
        }else{
            $valor = "Existe salário de motorista não preenchido.";
        }
        return ['result' => $boValidate, 'codigo_parametro' => 'SALARIO_MEDIO_MOTORISTA', 'valor' => $valor, 'modulo' => 'Motoristas', 'motoristas_invalidos' => $cpfsInvalidos];
        //return ['result' => $boValidate, $arRetorno];
    }
    
    public function processarSalarioMonitores($idRota, $codigoCidade){
        $arMonitores = $this->getMonitoresDaRota($idRota, $codigoCidade);
        $count = 0;
        $salario = 0;
        $cpfsInvalidos = [];
        $boValidate = true;
        foreach ($arMonitores as $rowMonitor){
            if($rowMonitor->salario == "" || empty($rowMonitor->salario)){
                $boValidate = false;
                $rowMonitor->salario = 0;
                $cpfsInvalidos[] = $rowMonitor->cpf;
            }
            $salario += $rowMonitor->salario;
            $count++;
        }
        
        if($boValidate){
            if($count > 0){
                $valor = (float) $salario / $count;
            }else{
                $valor = (float) $salario;
            }
            
        }else{
            $valor = "Existe salário de monitor não preenchido.";
        }
        return ['result' => $boValidate, 'codigo_parametro' => 'SALARIO_MEDIO_MONITORES', 'valor' => $valor, 'modulo' => 'Monitores', 'monitores_invalidos' => $cpfsInvalidos];
    }
    
    public function processarParametrosFrotaRodoviaria($idRota, $codigoCidade){
        $arVeiculos = $this->getVeiculosFrotaRodoviaria($idRota, $codigoCidade);
        $arParametrosGeral = [];
        $count = 0;
        $ipva = 0;
        $dpvat = 0;
        $consumo = 0;
        $numeroPneus = 0;
        $vidaUtilPneu = 0;
        $veiculosInvalidosIPVA = [];
        $veiculosInvalidosDPVAT = [];
        $veiculosInvalidosConsumo = [];
        $veiculosInvalidosNumeroPneus = [];
        $veiculosInvalidosVidaUtilPneus = [];
        $boValidateIPVA = true;
        $boValidateDPVAT = true;
        $boValidateNumPneus = true;
        $boValidateVidaUtilPneu = true;
        $boValidateConsumo = true;
        
        var_dump($arVeiculos);
        foreach ($arVeiculos as $rowVeiculo){
            if($rowVeiculo->ipva == "" || empty($rowVeiculo->ipva)){
                $boValidateIPVA = false;
                $rowVeiculo->ipva = 0;
                $veiculosInvalidosIPVA[] = $rowVeiculo->id_veiculo;
            }
            if($rowVeiculo->dpvat == "" || empty($rowVeiculo->dpvat)){
                $boValidateDPVAT = false;
                $rowVeiculo->dpvat = 0;
                $veiculosInvalidosDPVAT[] = $rowVeiculo->id_veiculo;
            }
            if($rowVeiculo->consumo == "" || empty($rowVeiculo->consumo)){
                $boValidateConsumo = false;
                $rowVeiculo->consumo = 0;
                $veiculosInvalidosConsumo[] = $rowVeiculo->id_veiculo;
            }
            if($rowVeiculo->numero_de_pneus == "" || empty($rowVeiculo->numero_de_pneus)){
                $boValidateNumPneus = false;
                $rowVeiculo->numero_de_pneus = 0;
                $veiculosInvalidosNumeroPneus[] = $rowVeiculo->id_veiculo;
            }
            if($rowVeiculo->vida_util_do_pneu == "" || empty($rowVeiculo->vida_util_do_pneu)){
                $boValidateVidaUtilPneu = false;
                $rowVeiculo->vida_util_do_pneu = 0;
                $veiculosInvalidosVidaUtilPneus[] = $rowVeiculo->id_veiculo;
            }
            $ipva += $rowVeiculo->ipva;
            $dpvat += $rowVeiculo->dpvat;
            $consumo += $rowVeiculo->consumo;
            $numeroPneus += $rowVeiculo->numero_de_pneus;
            $vidaUtilPneu += $rowVeiculo->vida_util_do_pneu;
            $count++;
        }
        
        if($boValidateIPVA){
            $valor = (float) $ipva;
        }else{
            $valor = "O campo IPVA do cadastro do veículo não está preenchido.";
        }
        $arParametrosGeral[] = ['result' => $boValidateIPVA, 'codigo_parametro' => 'IPVA_FROTA', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidosIPVA];       
        
        if($boValidateDPVAT){
            $valor = (float) $dpvat;
        }else{
            $valor = "O campo DPVAT do cadastro do veículo não está preenchido.";
        }
        $arParametrosGeral[] = ['result' => $boValidateDPVAT, 'codigo_parametro' => 'DPVAT_FROTA', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidosDPVAT];     
        
        if($boValidateConsumo){
            $valor = (float) $consumo;
        }else{
            $valor = "O campo Consumo do cadastro do veículo não está preenchido.";
        }
        $arParametrosGeral[] = ['result' => $boValidateConsumo, 'codigo_parametro' => 'CFT_CONSUMO_COMBUSTIVEL', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidosConsumo];
        
        if($boValidateNumPneus){
            $valor = (float) $numeroPneus;
        }else{
            $valor = "O campo Consumo do cadastro do veículo não está preenchido.";
        }
        $arParametrosGeral[] = ['result' => $boValidateNumPneus, 'codigo_parametro' => 'NUMERO_PNEUS', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidosNumeroPneus];
        
        if($boValidateVidaUtilPneu){
            $valor = (float) $vidaUtilPneu;
        }else{
            $valor = "O campo Vida Útil do Pneu do cadastro do veículo não está preenchido.";
        }
        $arParametrosGeral[] = ['result' => $boValidateVidaUtilPneu, 'codigo_parametro' => 'VIDA_UTIL_PNEU', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidosVidaUtilPneus];
        
        return $arParametrosGeral;
    }
    
    public function processarParametrosFrotaAquaviaria($idRota, $codigoCidade){
        $arVeiculos = $this->getVeiculosFrotaAquaviaria($idRota, $codigoCidade);
        $count = 0;
        $potenciaMotor = 0;
        $arValidateGeral = [];
        $veiculosInvalidosPotencia = [];
        $boValidatePotencia = true;
        foreach ($arVeiculos as $rowVeiculo){
            if($rowVeiculo->potencia_do_motor == "" || empty($rowVeiculo->potencia_do_motor)){
                $boValidatePotencia = false;
                $rowVeiculo->potencia_do_motor = 0;
                $veiculosInvalidosPotencia[] = $rowVeiculo->id_veiculo;
            }
            $potenciaMotor += $rowVeiculo->potencia_do_motor;
            $count++;
        }
        
        if($boValidatePotencia){
            $valor = (float) $potenciaMotor;
        }else{
            $valor = "Campo Potência do motor não preenchido.";
        }
        $arValidateGeral[] = ['result' => $boValidatePotencia, 'codigo_parametro' => 'POTENCIA_MOTOR', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidosPotencia];
        
        
        return $arValidateGeral;
    }
    
    public function processarParametrosFrotaGeral($idRota, $codigoCidade){
        $arVeiculos = $this->getVeiculosFrotaGeral($idRota, $codigoCidade);
        $count = 0;
        $seguroAnual = 0;
        $preco = 0;
        $idadeVeiculo = 0;
        $arValidateGeral = [];
        $veiculosInvalidosSeguroAnual = [];
        $veiculosInvalidosPreco = [];
        $veiculosInvalidoAno    = [];
        $boValidateSeguroAnual = true;
        $boValidatePreco = true;
        $boValidateAnoVeiculo = true;
        foreach ($arVeiculos as $rowVeiculo){
            if($rowVeiculo->seguro_anual == "" || empty($rowVeiculo->seguro_anual)){
                $boValidateSeguroAnual = false;
                $rowVeiculo->seguro_anual = 0;
                $veiculosInvalidosSeguroAnual[] = $rowVeiculo->id_veiculo;
            }
            if($rowVeiculo->preco == "" || empty($rowVeiculo->preco)){
                $boValidatePreco = false;
                $rowVeiculo->preco = 0;
                $veiculosInvalidosPreco[] = $rowVeiculo->id_veiculo;
            }
            if($rowVeiculo->ano == "" || empty($rowVeiculo->ano)){
                $boValidateAnoVeiculo = false;
                $rowVeiculo->ano = 0;
                $veiculosInvalidoAno[] = $rowVeiculo->id_veiculo;
            }else{
                $idadeVeiculo += (date("Y") - $rowVeiculo->ano);
            }
            $seguroAnual += $rowVeiculo->seguro_anual;
            $preco += $rowVeiculo->preco;
            $count++;
        }
        
        if($boValidateSeguroAnual){
            $valor = (float) $seguroAnual;
        }else{
            $valor = "Campo Seguro anual não preenchido no cadastro de veiculos.";
        }
        $arValidateGeral[] = ['result' => $boValidateSeguroAnual, 'codigo_parametro' => 'SRC_FROTA', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidosSeguroAnual];
        
        if($boValidatePreco){
            if($count > 0){
                $valor = (float) $preco / $count;
            }else{
                $valor = (float) $preco;
            }
        }else{
            $valor = "Campo Preço do veículo não preenchido.";
        }
        $arValidateGeral[] = ['result' => $boValidatePreco, 'codigo_parametro' => 'PRECO_MEDIO_VEICULOS', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidosPreco];
        
        if($boValidateAnoVeiculo){
            if($count > 0){
                $valor = (float) $idadeVeiculo / $count;
            }else{
                $valor = (float) $idadeVeiculo;
            }
        }else{
            $valor = "Campo Ano do veículo não preenchido.";
        }
        $arValidateGeral[] = ['result' => $boValidateAnoVeiculo, 'codigo_parametro' => 'IDADE_MEDIA_VEICULOS', 'valor' => $valor, 'modulo' => 'Frota', 'veiculos_invalidos' => $veiculosInvalidoAno];
        
        $arValidateGeral[] = $this->processarParametroPrecoMedioCombustivel($arVeiculos, $codigoCidade);
        
        return $arValidateGeral;
    }
    
    private function processarParametroPrecoMedioCombustivel($arVeiculos, $codigoCidade){
        $dbSetePGParametros = new \Db\SetePG\SeteParametros();
        $precoMedioGasolina = $dbSetePGParametros->getById(['codigo_cidade' => $codigoCidade, 'codigo_parametro' => $dbSetePGParametros::PRECO_MEDIO_GASOLINA]);
        $precoMedioDiesel = $dbSetePGParametros->getById(['codigo_cidade' => $codigoCidade, 'codigo_parametro' => $dbSetePGParametros::PRECO_MEDIO_DIESEL]);
        $precoMedioEtanol = $dbSetePGParametros->getById(['codigo_cidade' => $codigoCidade, 'codigo_parametro' => $dbSetePGParametros::PRECO_MEDIO_ETANOL]);
        $precoMedioGasNatural = $dbSetePGParametros->getById(['codigo_cidade' => $codigoCidade, 'codigo_parametro' => $dbSetePGParametros::PRECO_MEDIO_GAS_NATURAL]);
        $precoMedioOutros = $dbSetePGParametros->getById(['codigo_cidade' => $codigoCidade, 'codigo_parametro' => $dbSetePGParametros::PRECO_MEDIO_OUTRO_COMBUSTIVEL]);
        
        if($arVeiculos[0]['tipo_combustivel'] === \Db\Enum\TipoCombustivel::GASOLINA && (empty($precoMedioGasolina) || $precoMedioGasolina == "")){
           return ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => 'Parâmetro não informado!'];
        }else{
            ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => (float) $precoMedioGasolina];
        }
        
        if($arVeiculos[0]['tipo_combustivel'] === \Db\Enum\TipoCombustivel::DIESEL && (empty($precoMedioDiesel) || $precoMedioDiesel == "")){
           return ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => 'Parâmetro não informado!'];
        }else{
           return ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => (float) $precoMedioDiesel];
        }
        
        if($arVeiculos[0]['tipo_combustivel'] === \Db\Enum\TipoCombustivel::ETANOL && (empty($precoMedioEtanol) || $precoMedioEtanol == "")){
           return ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => 'Parâmetro não informado!'];
        }else{
           return ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => (float) $precoMedioEtanol];
        }
        
        if($arVeiculos[0]['tipo_combustivel'] === \Db\Enum\TipoCombustivel::GAS_NATURAL && (empty($precoMedioGasNatural) || $precoMedioGasNatural == "")){
           return ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => 'Parâmetro não informado!'];
        }else{
           return ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => (float) $precoMedioGasNatural];
        }
        
        if($arVeiculos[0]['tipo_combustivel'] === \Db\Enum\TipoCombustivel::OUTRO && (empty($precoMedioOutros) || $precoMedioOutros == "")){
           return ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => 'Parâmetro não informado!'];
        }else{
           return ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => (float) $precoMedioOutros];
        }
        
    }
    
    public function processarParametrosRota($idRota, $codigoCidade){
        $qtdMotoristas = $this->getQtdMotoristas($idRota, $codigoCidade);
        $qtdVeiculos   = $this->getQtdVeiculos($idRota, $codigoCidade);
        $qtdAlunos     = $this->getQtdAlunos($idRota, $codigoCidade);
        $qtdMonitores  = $this->getQtdMonitores($idRota, $codigoCidade);
        
        $arValidateGeral[] = ['result' => true, 'codigo_parametro' => 'NUM_MOTORISTAS', 'valor' => $qtdMotoristas, 'modulo' => 'Rota'];
        $arValidateGeral[] = ['result' => true, 'codigo_parametro' => 'NUM_VEICULOS', 'valor' => $qtdVeiculos, 'modulo' => 'Rota'];
        $arValidateGeral[] = ['result' => true, 'codigo_parametro' => 'NUM_ALUNOS', 'valor' => $qtdAlunos, 'modulo' => 'Rota'];
        $arValidateGeral[] = ['result' => true, 'codigo_parametro' => 'NUM_MONITORES', 'valor' => $qtdMonitores, 'modulo' => 'Monitores'];
        
        return $arValidateGeral;
    }
    
    
    

}
