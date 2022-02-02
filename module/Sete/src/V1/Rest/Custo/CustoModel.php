<?php

namespace Sete\V1\Rest\Custo;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CustoModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Db\SetePG\SeteAlunos();
    }

    public function getAll($codigoMunicipio) {
        return [];
    }

    public function getById($codigoCidade, $idAluno) {
        return [];
    }

    public function prepareInsert($arPost) {
        return [];
    }

    public function validarInsert($arPost) {
        return [];
    }

    private function validarParametrosInsertAluno($arPost) {
        return [];
    }

    public function validarUpdate($arPost, $idAluno) {
        return [];
    }

    public function prepareUpdate($codigoCidade, $idAluno, $arPost) {
        return [];
    }

    public function checarRotaAndCidadeExistem($codigoCidade, $idRota) {
        $dbGlbMunicipio = new \Db\SetePG\GlbMunicipios();
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $boValidate['result'] = true;
        if (!$dbGlbMunicipio->municipioExiste($codigoCidade)) {
            $boValidate['result'] = false;
            $boValidate['messages'] = "Código do municipio informado não existe!";
            $boValidate['http_code'] = 404;
        } else if (!$dbSeteRotas->rotaExiste($idRota, $codigoCidade)) {
            $boValidate['result'] = false;
            $boValidate['messages'] = "O Id da Rota informado não existe!";
            $boValidate['http_code'] = 404;
        }
        return $boValidate;
    }

    public function validarParametrosCusto($codigoCidade, $idRota) {
        $dbSeteParametros = new \Db\SetePG\SeteParametros();
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $arParametrosGlobais = $dbSeteParametros->getParametros($codigoCidade);
        $arRota = $dbSeteRotas->getById([
            'codigo_cidade' => $codigoCidade,
            'id_rota' => $idRota
        ]);
        if (!in_array($arRota['tipo'], [\Db\Enum\Rota\Tipo::AQUAVIARIO, \Db\Enum\Rota\Tipo::RODOVIARIA])) {
            return ['result' => false, 'modulo' => 'Rotas', 'valor' => 'Tipo da Rota (Mista) não configurado para o cálculo.'];
        } else {
            $boValidateParametros = $this->validarDadosParametrosParaCalculoCusto($arParametrosGlobais, $arRota['tipo']);
            if ($boValidateParametros['result']) {
                $boValidateParametrosCadastros = $this->validarDadosCadastrosParaCalculoCusto($arRota);
                //MESCLA O RETORNO DOS PARAMETROS GLOBAIS COM O RETORNO DO PROCESSAMENTO DOS DADOS DE CADASTRO DO MOTORISTA
                foreach ($boValidateParametrosCadastros as $parametrosCadastro) {
                    array_push($boValidateParametros['params'], $parametrosCadastro);
                }

                return $boValidateParametros;
            } else {
                return $boValidateParametros;
            }
        }
    }

    private function validarDadosCadastrosParaCalculoCusto($arRota) {
        $arValidateGeral = [];
        $arValidateFrotaRodoviaria = [];
        $arValidateFrotaAquaviaria = [];
        $dbSeteValidacaoDadosCustos = new \Db\SetePG\SeteValidacaoDadosCusto();
        $idRota = $arRota['id_rota'];
        $tipoRota = $arRota['tipo'];
        $codigoCidade = $arRota['codigo_cidade'];
        $arValidateMotoristas = $dbSeteValidacaoDadosCustos->processarSalarioMotorista($idRota, $codigoCidade);
        array_push($arValidateGeral, $arValidateMotoristas);
        $arValidateMonitores = $dbSeteValidacaoDadosCustos->processarSalarioMonitores($idRota, $codigoCidade);
        array_push($arValidateGeral, $arValidateMonitores);
        if ($tipoRota == \Db\Enum\Rota\Tipo::RODOVIARIA) {
            $arValidateFrotaRodoviaria = $dbSeteValidacaoDadosCustos->processarParametrosFrotaRodoviaria($idRota, $codigoCidade);
            foreach ($arValidateFrotaRodoviaria as $parametrosFrotaRodoviario) {
                array_push($arValidateGeral, $parametrosFrotaRodoviario);
            }
            array_push($arValidateGeral, $this->validarKMMensalRota($arRota));
        }
        if ($tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $arValidateFrotaAquaviaria = $dbSeteValidacaoDadosCustos->processarParametrosFrotaAquaviaria($idRota, $codigoCidade);
            foreach ($arValidateFrotaAquaviaria as $parametrosFrotaAquaviaria) {
                array_push($arValidateGeral, $parametrosFrotaAquaviaria);
            }
        }

        $arValidateFrotaGeral = $dbSeteValidacaoDadosCustos->processarParametrosFrotaGeral($idRota, $codigoCidade);
        foreach ($arValidateFrotaGeral as $parametrosFrotaGeral) {
            array_push($arValidateGeral, $parametrosFrotaGeral);
        }
        
        $arValidateRota = $dbSeteValidacaoDadosCustos->processarParametrosRota($idRota, $codigoCidade);
        foreach ($arValidateRota as $parametrosRota) {
            array_push($arValidateGeral, $parametrosRota);
        }

        return $arValidateGeral;
    }
    
    private function validarKMMensalRota($arRota){
        if($arRota['km'] == "" || empty($arRota['km'])){
            return ['result' => false, 'modulo' => 'Rotas', 'codigo_parametro' => 'KM_MENSAL_ROTA', 'valor' => 'Campo KM não informado no cadastro da rota!'];
        }else{
            //Tabela sete_rotas, campo km. Resultado é a somatória por 20 dias (x20).
            $km = $arRota['km'] * 20;
            return ['result' => true, 'modulo' => 'Rotas', 'codigo_parametro' => 'KM_MENSAL_ROTA', 'valor' => $km];
        }
    }

    private function validarDadosParametrosParaCalculoCusto($arParametros, $tipoRota) {
        $arValidacao = [];
        $boValidate = true;
        if (empty($arParametros['PERC_ENCARGO_SOCIAIS'])) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_ENCARGO_SOCIAIS', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_ENCARGO_SOCIAIS', 'valor' => (float) $arParametros['PERC_ENCARGO_SOCIAIS']];
        }
        if (empty($arParametros['PERC_CFT_CUSTO_MANUTENCAO_RODO']) && $tipoRota == \Db\Enum\Rota\Tipo::RODOVIARIA) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_CFT_CUSTO_MANUTENCAO_RODO', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_CFT_CUSTO_MANUTENCAO_RODO', 'valor' => (float) $arParametros['PERC_CFT_CUSTO_MANUTENCAO_RODO']];
        }
        if (empty($arParametros['PERC_CFT_CUSTO_MANUTENCAO_AQUA']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_CFT_CUSTO_MANUTENCAO_AQUA', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_CFT_CUSTO_MANUTENCAO_AQUA', 'valor' => (float) $arParametros['PERC_CFT_CUSTO_MANUTENCAO_AQUA']];
        }
        if (empty($arParametros['CONSUMO_COMBUSTIVEL_AQUAVIARIO']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'CONSUMO_COMBUSTIVEL_AQUAVIARIO', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'CONSUMO_COMBUSTIVEL_AQUAVIARIO', 'valor' => (float) $arParametros['CONSUMO_COMBUSTIVEL_AQUAVIARIO']];
        }
        if (empty($arParametros['VIDA_UTIL_RODO']) && $tipoRota == \Db\Enum\Rota\Tipo::RODOVIARIA) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'VIDA_UTIL_RODO', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'VIDA_UTIL_RODO', 'valor' => (float) $arParametros['VIDA_UTIL_RODO']];
        }
        if (empty($arParametros['VIDA_UTIL_AQUA']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'VIDA_UTIL_AQUA', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'VIDA_UTIL_AQUA', 'valor' => (float) $arParametros['VIDA_UTIL_AQUA']];
        }
        if (empty($arParametros['PERC_RESIDUAL_RODO']) && $tipoRota == \Db\Enum\Rota\Tipo::RODOVIARIA) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_RESIDUAL_RODO', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_RESIDUAL_RODO', 'valor' => (float) $arParametros['PERC_RESIDUAL_RODO']];
        }
        if (empty($arParametros['PERC_RESIDUAL_AQUA']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_RESIDUAL_AQUA', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_RESIDUAL_AQUA', 'valor' => (float) $arParametros['PERC_RESIDUAL_AQUA']];
        }
        if (empty($arParametros['PERC_TRC'])) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_TRC', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_TRC', 'valor' => (float) $arParametros['PERC_TRC']];
        }
        /*if (empty($arParametros['PRECO_MEDIO_COMBUSTIVEIS'])) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_COMBUSTIVEIS', 'valor' => (float) $arParametros['PRECO_MEDIO_COMBUSTIVEIS']];
        }*/
        if (empty($arParametros['CFT_CONSUMO_OLEO_LUBRIFICANTE'])) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'CFT_CONSUMO_OLEO_LUBRIFICANTE', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'CFT_CONSUMO_OLEO_LUBRIFICANTE', 'valor' => (float) $arParametros['CFT_CONSUMO_OLEO_LUBRIFICANTE']];
        }
        if (empty($arParametros['PRECO_MEDIO_PNEUS']) && $tipoRota == \Db\Enum\Rota\Tipo::RODOVIARIA) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_PNEUS', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_PNEUS', 'valor' => (float) $arParametros['PRECO_MEDIO_PNEUS']];
        }
        if (empty($arParametros['PRECO_MEDIO_RECAPAGEM']) && $tipoRota == \Db\Enum\Rota\Tipo::RODOVIARIA) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_RECAPAGEM', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_RECAPAGEM', 'valor' => (float) $arParametros['PRECO_MEDIO_RECAPAGEM']];
        }
        if (empty($arParametros['NUM_RECAPAGEM']) && $tipoRota == \Db\Enum\Rota\Tipo::RODOVIARIA) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'NUM_RECAPAGEM', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'NUM_RECAPAGEM', 'valor' => (float) $arParametros['NUM_RECAPAGEM']];
        }
        if (empty($arParametros['CFT_CONSUMO_PECAS'])) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'CFT_CONSUMO_PECAS', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'CFT_CONSUMO_PECAS', 'valor' => (float) $arParametros['CFT_CONSUMO_PECAS']];
        }
        if (empty($arParametros['PERC_SEGURO_AQUA']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_SEGURO_AQUA', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_SEGURO_AQUA', 'valor' => (float) $arParametros['PERC_SEGURO_AQUA']];
        }
        if (empty($arParametros['DENSIDADE_COMBUSTIVEL']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'DENSIDADE_COMBUSTIVEL', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'DENSIDADE_COMBUSTIVEL', 'valor' => (float) $arParametros['DENSIDADE_COMBUSTIVEL']];
        }
        if (empty($arParametros['CONSUMO_LUBRIFICANTE']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'CONSUMO_LUBRIFICANTE', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'CONSUMO_LUBRIFICANTE', 'valor' => (float) $arParametros['CONSUMO_LUBRIFICANTE']];
        }
        if (empty($arParametros['DENSIDADE_LUBRIFICANTE']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'DENSIDADE_LUBRIFICANTE', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'DENSIDADE_LUBRIFICANTE', 'valor' => (float) $arParametros['DENSIDADE_LUBRIFICANTE']];
        }
        if (empty($arParametros['PRECO_MEDIO_LUBRIFICANTE']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_LUBRIFICANTE', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PRECO_MEDIO_LUBRIFICANTE', 'valor' => (float) $arParametros['PRECO_MEDIO_LUBRIFICANTE']];
        }
        if (empty($arParametros['PERC_MANUTENCAO_EMBARCACAO']) && $tipoRota == \Db\Enum\Rota\Tipo::AQUAVIARIO) {
            $boValidate = false;
            $arValidacao[] = ['result' => false, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_MANUTENCAO_EMBARCACAO', 'valor' => 'Parâmetro não informado!'];
        } else {
            $arValidacao[] = ['result' => true, 'modulo' => 'Parâmetros', 'codigo_parametro' => 'PERC_MANUTENCAO_EMBARCACAO', 'valor' => (float) $arParametros['PERC_MANUTENCAO_EMBARCACAO']];
        }
        return ['result' => $boValidate, 'params' => $arValidacao];
    }

    public function getListaPaginada($pagina, $busca = "") {
        $qtdPerPage = 20;
        $totalRegistros = $this->_entity->getTotalMunicipios($busca);
        $qtdPaginas = ceil($totalRegistros / $qtdPerPage);
        $offset = ($qtdPerPage * $pagina) - $qtdPerPage;
        $arData = $this->_entity->getMunicipiosLista($offset, $qtdPerPage, $busca);
        return [
            'qtd_registros' => (int) $totalRegistros,
            'pages' => (int) $qtdPaginas,
            'reg_por_pagina' => (int) $qtdPerPage,
            'pg_atual' => (int) $pagina,
            'registros' => $arData
        ];
    }

}
