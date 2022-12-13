<?php

namespace Sete\V1\Rest\Graficos;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class GraficosResource extends API {

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data) {
        return new ApiProblem(405, 'The POST method has not been defined');
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id) {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data) {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id) {
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $dbGlbMunicipios = new \Db\SetePG\GlbMunicipios();
        $codigoCidade = $arParams['codigo_cidade'];
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro codigo_cidade deve ser informado!"], false);
        } else if (!$dbGlbMunicipios->municipioExiste($codigoCidade)) {
            $this->populaResposta(404, ['result' => false, 'messages' => "O municipio informado não existe!"], false);
        } else if (!$this->usuarioPodeAcessarCidade($codigoCidade)) {
            $this->populaResposta(403, ['result' => false, 'messages' => "Usuário sem permissão para acessar o municipio informado!"], false);
        } else {
            if (isset($arParams['graficos_id'])) {
                $rota = $arParams['graficos_id'];
                switch ($rota) {
                    case 'alunos':
                        $arResult = $this->getDadosAlunos($codigoCidade);
                        break;
                    case 'rotas':
                        $arResult = $this->getDadosRotas($codigoCidade);
                        break;
                    case 'escolas':
                        $arResult = $this->getDadosEscolas($codigoCidade);
                        break;
                    case 'veiculos':
                        $arResult = $this->getDadosVeiculos($codigoCidade);
                        break;
                    default:
                        $arResult = ['result' => false, 'messages' => "Recurso não existe!"];
                        break;
                }
                $this->populaResposta(count($arResult) >= 1 ? 200 : 404, $arResult, false);
            }
        }
    }
    
    private function getDadosRotas($codigoCidade){
        $arResult['data'][0] = $this->getInfosRotasPorTipo($codigoCidade);
        array_push($arResult['data'], $this->getInfosKilometragemRotas($codigoCidade));
        array_push($arResult['data'], $this->getInfosKilometragemTotalRotas($codigoCidade));
        array_push($arResult['data'], $this->getInfosTempoRotas($codigoCidade));
        array_push($arResult['data'], $this->getInfosTempoTotalRotas($codigoCidade));
        array_push($arResult['data'], $this->getInfosRotasPorTurno($codigoCidade)); 
        array_push($arResult['data'], $this->getInfosRotasPorDificuldade($codigoCidade)); 
        return $arResult;
    }
    
    private function getInfosRotasPorTipo($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosRotaPorTipo($codigoCidade);
        $arResposta['nome'] = "Rotas";
        $arResposta['titulo'] = "Rotas por Tipo";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosKilometragemRotas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosKilometragemRota($codigoCidade);
        $arResposta['nome'] = "Quilometragem das Rotas";
        $arResposta['titulo'] = "Valores da menor, média e maior quilometragem das rotas";
        $arResposta['labels'] = ['Menor', 'Média', 'Maior'];
        $arResposta['values'] = [$arDados['menor'], $arDados['media'], $arDados['maior']];
        return $arResposta;
    }
    
    private function getInfosTempoRotas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosTempoRota($codigoCidade);
        $arResposta['nome'] = "Tempo da Viagem";
        $arResposta['titulo'] = "Valores do menor, médio e maior tempo gasto pelas rotas";
        $arResposta['labels'] = ['Menor', 'Média', 'Maior'];
        $arResposta['values'] = [$arDados['menor'], $arDados['media'], $arDados['maior']];
        return $arResposta;
    }
    
    private function getInfosRotasPorTurno($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosRotasPorTurno($codigoCidade);
        $arResposta['nome'] = "Turno";
        $arResposta['titulo'] = "Distribuição de rotas por turno";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosRotasPorDificuldade($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosRotasPorDificuldade($codigoCidade);
        $arResposta['nome'] = "Dificuldades Atravessadas";
        $arResposta['titulo'] = "Quantitativo das dificuldades atravessadas pelas rotas";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosTempoTotalRotas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $tempoTotal = $modelGraficos->getDadosTempoTotalRota($codigoCidade);
        $arResposta['nome'] = "Tempo Total";
        $arResposta['titulo'] = "Tempo total percorrido pela rota";
        $arResposta['labels'] = ['Total'];
        $arResposta['values'] = [$tempoTotal];
        return $arResposta;
    }
    
    private function getInfosKilometragemTotalRotas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $kilometragemTotal = $modelGraficos->getDadosKilometragemTotalRota($codigoCidade);
        $arResposta['nome'] = "Quilometragem das Rotas";
        $arResposta['titulo'] = "Quilometragem total percorrida pela rota";
        $arResposta['labels'] = ['Total'];
        $arResposta['values'] = [$kilometragemTotal];
        return $arResposta;
    }

    private function getDadosAlunos($codigoCidade) {
        $arResult['data'][0] = $this->getInfosAlunosAtendimento($codigoCidade);
        array_push($arResult['data'], $this->getInfosAlunosEscola($codigoCidade));
        array_push($arResult['data'], $this->getInfosAlunosRota($codigoCidade));
        array_push($arResult['data'], $this->getInfosAlunosEscolaridade($codigoCidade));
        array_push($arResult['data'], $this->getInfosAlunosTurno($codigoCidade));
        array_push($arResult['data'], $this->getInfosAlunosResidencia($codigoCidade));
        array_push($arResult['data'], $this->getInfosAlunosCor($codigoCidade));
        array_push($arResult['data'], $this->getInfosAlunosSexo($codigoCidade));
        array_push($arResult['data'], $this->getInfosAlunosResponsavel($codigoCidade));
        
        return $arResult;
    }

    private function getInfosAlunosAtendimento($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosAlunos($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Atendimento";
        $arResposta['titulo'] = "Porcentagem de Alunos Atendidos (Cadastrados no Sistema)";
        $arResposta['labels'] = ["Sem Rota Cadastrada", "Com Rota Cadastrada"];
        $arResposta['values'] = [$arDados['alunos_sem_rota'], $arDados['alunos_com_rota']];
        return $arResposta;
    }

    private function getInfosAlunosEscola($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosEscolas($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Escolas";
        $arResposta['titulo'] = "Número Médio de Alunos por Escola";
        $arResposta['labels'] = ["Número Médio de Alunos Transportados por Escola"];
        $arResposta['values'] = [$arDados['alunos_transportados_escola']];
        return $arResposta;
    }

    private function getInfosAlunosRota($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosRotas($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Rotas";
        $arResposta['titulo'] = "Número Médio de Alunos por Rota";
        $arResposta['labels'] = ["Número Médio de Alunos Transportados por Rota"];
        $arResposta['values'] = [$arDados['alunos_por_rota']];
        return $arResposta;
    }

    private function getInfosAlunosEscolaridade($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosEscolaridade($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Nível de Escolaridade";
        $arResposta['titulo'] = "Distribuição de Alunos por Nível de Escolaridade";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];

        return $arResposta;
    }

    private function getInfosAlunosTurno($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosTurno($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Turno de Aula";
        $arResposta['titulo'] = "Distribuição de Alunos por Turno de Ensino";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    private function getInfosAlunosResidencia($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosResidiencia($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Área de Residência";
        $arResposta['titulo'] = "Porcentagem de Alunos por Localização";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    private function getInfosAlunosCor($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosCor($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Cor";
        $arResposta['titulo'] = "Porcentagem de Alunos por Cor/Raça";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    private function getInfosAlunosSexo($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosSexo($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Sexo";
        $arResposta['titulo'] = "Porcentagem de Alunos por Sexo";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    private function getInfosAlunosResponsavel($codigoCidade) {
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosResponsavel($codigoCidade);
        //$arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Responsável";
        $arResposta['titulo'] = "Porcentagem de Alunos por Categoria de Responsável";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getDadosEscolas($codigoCidade){
        $arResult['data'][0] = $this->getInfosLocalidadesEscolas($codigoCidade);
        array_push($arResult['data'], $this->getInfosDependenciaEscolas($codigoCidade));
        array_push($arResult['data'], $this->getInfosNivelEnsinoEscolas($codigoCidade));
        array_push($arResult['data'], $this->getInfosTipoEnsinoEscolas($codigoCidade));
        array_push($arResult['data'], $this->getInfosHorarioFuncionamentoEscolas($codigoCidade));
        return $arResult;
    }
    
    private function getInfosLocalidadesEscolas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosLocalidadesEscolas($codigoCidade);
        $arResposta['nome'] = "Localidade";
        $arResposta['titulo'] = "Porcentagem de Escolas por Localidades Cadastradas no Sistema";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosDependenciaEscolas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosDependenciaEscolas($codigoCidade);
        $arResposta['nome'] = "Dependência";
        $arResposta['titulo'] = "Porcentagem de Escolas por Dependência Cadastradas no Sistema";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosNivelEnsinoEscolas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosNivelEnsinoEscolas($codigoCidade);
        $arResposta['nome'] = "Nível de Ensino";
        $arResposta['titulo'] = "Porcentagem de Escolas por Nível de Ensino Cadastradas no Sistema";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosTipoEnsinoEscolas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosTipoEnsinoEscolas($codigoCidade);
        $arResposta['nome'] = "Tipo de Ensino";
        $arResposta['titulo'] = "Porcentagem de Escolas por Tipo de Ensino Cadastradas no Sistema";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosHorarioFuncionamentoEscolas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosHorarioFuncionamentoEscolas($codigoCidade);
        $arResposta['nome'] = "Horário de Funcionamento";
        $arResposta['titulo'] = "Porcentagem de Escolas por Horário de Funcionamento Cadastradas no Sistema";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getDadosVeiculos($codigoCidade){
        $arResult['data'][0] = $this->getInfosLotacaoMediaVeiculos($codigoCidade);
        array_push($arResult['data'], $this->getInfosCapacidadeMediaVeiculos($codigoCidade));
        array_push($arResult['data'], $this->getInfosVeiculosPorCategoria($codigoCidade));
        array_push($arResult['data'], $this->getInfosMediaIdadeVeiculos($codigoCidade));
        array_push($arResult['data'], $this->getInfosVeiculosPorMarcas($codigoCidade));
        array_push($arResult['data'], $this->getInfosVeiculosPorModelo($codigoCidade));
        array_push($arResult['data'], $this->getInfosVeiculosPorOrigem($codigoCidade));
        return $arResult;
    }
    
    private function getInfosLotacaoMediaVeiculos($codigoCidade){
        $modelGraficos = new GraficosModel();
        $mediaPasssageirosPorVeiculos = $modelGraficos->getDadosMediaDePassageirosPorVeiculo($codigoCidade);
        $arResposta['nome'] = "Lotação Média";
        $arResposta['titulo'] = "Média de passageiros transportados por veículo";
        $arResposta['labels'] = ['Média'];
        $arResposta['values'] = [$mediaPasssageirosPorVeiculos];
        return $arResposta;
    }
    
    private function getInfosCapacidadeMediaVeiculos($codigoCidade){
        $modelGraficos = new GraficosModel();
        $mediaCapacidadeVeiculos = $modelGraficos->getDadosMediaCapacidadeDosVeiculos($codigoCidade);
        $arResposta['nome'] = "Capacidade Disponível";
        $arResposta['titulo'] = "Média da capacidade dos veículos";
        $arResposta['labels'] = ['Média'];
        $arResposta['values'] = [$mediaCapacidadeVeiculos];
        return $arResposta;
    }
    
    private function getInfosVeiculosPorCategoria($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosCategoriaVeiculos($codigoCidade);
        $arResposta['nome'] = "Categoria dos veículos";
        $arResposta['titulo'] = "Porcentagem de Veículos por Categoria";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosVeiculosPorOrigem($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosVeiculosPorOrigem($codigoCidade);
        $arResposta['nome'] = "Origem dos veículos";
        $arResposta['titulo'] = "Porcentagem de Veículos por Origem";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosMediaIdadeVeiculos($codigoCidade){
        $modelGraficos = new GraficosModel();
        $mediaIdadeVeiculos = $modelGraficos->getDadosMediaIdadeDosVeiculos($codigoCidade);
        $arResposta['nome'] = "Idade dos Veículos";
        $arResposta['titulo'] = "Média de idade dos veículos";
        $arResposta['labels'] = ['Média'];
        $arResposta['values'] = [$mediaIdadeVeiculos];
        return $arResposta;
    }
    
    private function getInfosVeiculosPorMarcas($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosVeiculosPorMarca($codigoCidade);
        $arResposta['nome'] = "Marca dos Veículos";
        $arResposta['titulo'] = "Porcentagem de Veículos por Marca";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }
    
    private function getInfosVeiculosPorModelo($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosVeiculosPorModelo($codigoCidade);
        $arResposta['nome'] = "Modelo dos veículos";
        $arResposta['titulo'] = "Porcentagem de Veículos por Modelo";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = []) {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data) {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data) {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data) {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data) {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }

}
