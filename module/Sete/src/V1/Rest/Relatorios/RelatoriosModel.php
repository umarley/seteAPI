<?php

namespace Sete\V1\Rest\Relatorios;

use PHPJasper\PHPJasper;

class RelatoriosModel {

    private $entidade;

    public function __construct() {
        $this->entidade = new \Db\Sistema\Relatorios();
    }

    public function getAll() {
        $arLista = $this->entidade->getLista();
        foreach ($arLista as $key => $row) {
            $arLista[$key]['parametros'] = $this->entidade->getParametrosRelatorio($row['id_relatorio']);
        }

        return $arLista;
    }

    public function validarParametros($arData, $idRelatorio) {
        $boValidate = true;
        $arErros = [];
        $arErros['parametros'] = [];
        if (!isset($arData->parametros)) {
            $boValidate = false;
            $arErros[] = "Os parâmetros devem ser enviado!";
        } else {
            foreach ($arData->parametros as $parametro) {
                $validateParametro = $this->entidade->parametroExiste($idRelatorio, $parametro['nome_parametro']);
                if (!$validateParametro) {
                    $boValidate = false;
                    $arErros['parametos'][] = "Parâmetro {$parametro['nome_parametro']} não existe para o relatório informado!";
                } else {
                    $isRequerido = $this->entidade->parametroIsRequerido($idRelatorio, $parametro['nome_parametro']);
                    if ($isRequerido && $parametro['valor_parametro'] == '') {
                        $boValidate = false;
                        $arErros['parametos'][] = "O valor do parâmetro {$parametro['nome_parametro']} deve ser informado!";
                    }
                }
            }
        }

        return ['result' => $boValidate, 'messages' => $arErros];
    }

    public function gerarRelatorio($idRelatorio, $arParametros, $codigoCidade, $accessToken) {
        $rowRelatorio = $this->entidade->getById($idRelatorio);
        $arParametros = $this->processarParametros($idRelatorio, $arParametros, $codigoCidade, $accessToken);
        try {
           $arResult = $this->jasper($rowRelatorio, $arParametros);
        } catch (Exception $ex) {
            $arResult = ['result' => false, 'messages' => "Falha ao gerar relatório. " . $ex->getMessage()];
        }
        
        return $arResult;
    }

    private function getPathJRXML($rowRelatorio) {
        $diretorioInput = getcwd() . '/jasper';
        $fileJRXML = str_replace("/", DIRECTORY_SEPARATOR, $rowRelatorio['path_jasper']);
        $sCaminho = $diretorioInput . DIRECTORY_SEPARATOR . $fileJRXML;
        return $sCaminho;
    }

    private function getDiretorioOutput() {
        $output = getcwd() . "/public/reports/";
        return $output;
    }

    private function jasper($rowRelatorio, $arParametros = []) {
        $arDbConfig = $this->getConfigDB();
        $oJasperPHP = new PHPJasper();
        $input = $this->getPathJRXML($rowRelatorio);
        $output = $this->getDiretorioOutput();
        $options = ['pdf'];
        $oJasperPHP->process(
                $input, $output, [
            'format' => $options,
            'params' => $arParametros,
            'db_connection' => $arDbConfig
                ]
        )->execute();
        $fileJRXML = $rowRelatorio['path_jasper'];
        $sNomeFile = str_replace(['jrxml', 'jasper'], "pdf", $fileJRXML);
        $sNovoNomeRelatorioGerado = uniqid(str_replace(".pdf", "", $sNomeFile)) . ".pdf";
        rename($output . '/' . $sNomeFile, $output . '/' . $sNovoNomeRelatorioGerado);
        //copy($output . '/' . $sNovoNomeRelatorioGerado, "{$output}/{$sNovoNomeRelatorioGerado}");
        //unlink($output . '/' . $sNovoNomeRelatorioGerado);
        return ['result' => true, 'file' => $sNovoNomeRelatorioGerado, 'path' => 'reports/' . $sNovoNomeRelatorioGerado];
    }

    private function processarParametros($idRelatorio, $arParametros, $codigoCidade, $accessToken) {
        foreach ($arParametros as $valueParametro) {
            $parametroAtual = $this->entidade->getParametroByRelatorioAndNome($idRelatorio, $valueParametro['nome_parametro']);
            if ($parametroAtual['tipo'] === $this->entidade::TIPO_DATA || $parametroAtual['tipo'] === $this->entidade::TIPO_DATARANGE) {
                if ($valueParametro['nome_parametro'] === 'dataInicio') {
                    $arParametros[$valueParametro['nome_parametro'] . "Sql"] = $this->formataDataSQL($valueParametro['valor_parametro']) . " 00:00:00";
                } else if ($valueParametro['nome_parametro'] === 'dataFinal') {
                    $arParametros[$valueParametro['nome_parametro'] . "Sql"] = $this->formataDataSQL($valueParametro['valor_parametro']) . " 23:59:59";
                } else {
                    $arParametros[$valueParametro['nome_parametro'] . "Sql"] = $this->formataDataSQL($valueParametro['valor_parametro']);
                }
            }
            if ($parametroAtual['requerido'] === $this->entidade::NAO_REQUERIDO && empty($arParametros[$valueParametro['nome_parametro']])) {
                unset($arParametros[$valueParametro['nome_parametro']]);
            }
        }
        $arParametros['codigo_cidade'] = $codigoCidade;
        $arParametros['usuario_sistema'] = $this->getUsuarioSistema($accessToken);
        return $arParametros;
    }
    
    private function getUsuarioSistema($accessToken){
        $dbUsuario = new \Db\SetePG\SeteUsuarios();
        $arUsuario = $dbUsuario->getUsuarioByAccessToken($accessToken);
        $usuario = explode("@", $arUsuario['email']);
        return $usuario[0];
    }

    private function getConfigDB() {

        $arConfig = $this->entidade->getDBConfig();
        $dbConfig = [
            'driver' => 'postgres', // mysql
            'username' => $arConfig['username'],
            'host' => $arConfig['hostname'],
            'database' => $arConfig['database'],
            'password' => $arConfig['password'],
            'port' => '5432',
            'jdbc_driver' => __DIR__ . '/jasper/jdbc/postgresql-42.2.9.jar'
        ];
        return $dbConfig;
    }

    private function formataDataSQL($data) {
        return implode('-', array_reverse(explode("/", $data)));
    }

}
