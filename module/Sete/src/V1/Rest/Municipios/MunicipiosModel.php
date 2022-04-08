<?php

namespace Sete\V1\Rest\Municipios;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MunicipiosModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new MunicipiosEntity();
    }

    public function getAll() {
        $cacheRedis = new \Db\Core\CacheRedis();
        $cache = $cacheRedis->criaCacheAdapter(86400);
        $existeCache = $cache->getItem('mapaMunicipios');
        if (empty($existeCache)) {
            $arDados = $this->_entity->getLista();
            foreach ($arDados as $key => $value) {
                $arDados[$key]['usa_sistema'] = $this->checarSeMunicipioEstaUsandoSistema($value['codigo_ibge']);
            }
            $cache->addItem('mapaMunicipios', json_encode($arDados));
        }else{
            $arDados = json_decode($existeCache, true);
        }
        return $arDados;
    }

    private function checarSeMunicipioEstaUsandoSistema($codigoMunicipio) {
        $dbSeteEscolas = new \Db\SetePG\SeteEscolas();
        $dbSeteAlunos = new \Db\SetePG\SeteAlunos();
        $dbSeteVeiculos = new \Db\SetePG\SeteVeiculos();
        $dbSeteRotas = new \Db\SetePG\SeteRotas();

        $qtdEscolas = $dbSeteEscolas->qtdEscolasAtendidas($codigoMunicipio);
        $qtdAlunos = $dbSeteAlunos->qtdAlunosAtendidos($codigoMunicipio);
        $qtdVeiculos = $dbSeteVeiculos->qtdVeiculosFuncionando($codigoMunicipio);
        $qtdVeiculosManutencao = $dbSeteVeiculos->qtdVeiculosManutencao($codigoMunicipio);
        $qtdRotas = $dbSeteRotas->qtdRotas($codigoMunicipio);

        $somatoria = ($qtdAlunos + $qtdEscolas + $qtdRotas + $qtdVeiculos + $qtdVeiculosManutencao);
        if ($somatoria > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getById($codigo) {
        $dbSeteEscolas = new \Db\SetePG\SeteEscolas();
        $dbSeteAlunos = new \Db\SetePG\SeteAlunos();
        $dbSeteVeiculos = new \Db\SetePG\SeteVeiculos();
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $arData = $this->_entity->getByCodigoIBGE($codigo);
        $arData['data'] = [
            'n_escolas' => $dbSeteEscolas->qtdEscolasAtendidas($codigo),
            'n_alunos' => $dbSeteAlunos->qtdAlunosAtendidos($codigo),
            'n_veiculos_funcionamento' => $dbSeteVeiculos->qtdVeiculosFuncionando($codigo),
            'n_veiculos_manutencao' => $dbSeteVeiculos->qtdVeiculosManutencao($codigo),
            'n_rotas' => $dbSeteRotas->qtdRotas($codigo),
            'n_rotas_kilometragem_total' => $dbSeteRotas->qtdRotasKilometragemTotal($codigo),
            'n_rotas_kilometragem_media' => $dbSeteRotas->qtdRotasKilometragemMedia($codigo),
            'n_tempo_medio_rota' => $dbSeteRotas->qtdRotasTempoMedio($codigo)
        ];
        return $arData;
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

    public function processarExcel() {
        $cacheRedis = new \Db\Core\CacheRedis();
        $cache = $cacheRedis->criaCacheAdapter(21600);
        $existeCache = $cache->getItem('excelMunicipios');
        if(empty($existeCache)){
            $arDados = $this->_entity->getMunicipiosListaExcel();
            $cache->addItem('excelMunicipios', json_encode($arDados));
        }else{
            $arDados = json_decode($existeCache, true);
        }
        return $this->gerarArquivoExcel($arDados);
    }

    private function gerarArquivoExcel($arDados) {
        $nomeExcel = uniqid("cidades-sete") . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Código IBGE')
                ->setCellValue('B1', 'Cidade')
                ->setCellValue('C1', 'Estado')
                ->setCellValue('D1', 'UF')
                ->setCellValue('E1', 'Quantidade Escolas')
                ->setCellValue('F1', 'Quantidade Alunos')
                ->setCellValue('G1', 'Qtd Veículos em funcionamento')
                ->setCellValue('H1', 'Qtd Veículos em Manutenção!')
                ->setCellValue('I1', 'Quantidade de Rotas')
                ->setCellValue('J1', 'Distância total das rotas (km)')
                ->setCellValue('K1', 'Distância média das rotas (km)')
                ->setCellValue('L1', 'Quantidade de motoristas')
                ->setCellValue('M1', 'Tempo médio das rotas (min)');
        $sheet->fromArray($arDados, NULL, 'A2');
        $messages = "";
        $writer = new Xlsx($spreadsheet);
        $pathArquivoGerado = "./data/{$nomeExcel}";
        try {
            $writer->save($pathArquivoGerado);
            $operacao = true;
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $ex) {
            $operacao = false;
            $messages = $ex->getMessage();
        }
        return $this->retornoProcessamentoExcel($operacao, $pathArquivoGerado, $nomeExcel, $messages);
    }

    private function retornoProcessamentoExcel($operacao, $pathArquivoGerado, $nomeExcel, $messages = "") {
        if ($operacao) {
            rename($pathArquivoGerado, $_SERVER['DOCUMENT_ROOT'] . "/storage/exports/{$nomeExcel}");
            return [
                'result' => true,
                'messages' => "Arquivo gerado com sucesso!",
                'file' => "storage/exports/{$nomeExcel}"
            ];
        } else {
            return [
                'result' => false,
                'messages' => "Falha ao gerar o arquivo!" . $messages
            ];
        }
    }

}
