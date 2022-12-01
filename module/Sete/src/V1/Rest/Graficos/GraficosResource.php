<?php
namespace Sete\V1\Rest\Graficos;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;


class GraficosResource extends API
{
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        return new ApiProblem(405, 'The POST method has not been defined');
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
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
            if(isset($arParams['graficos_id'])){
                $rota = $arParams['graficos_id'];
                switch ($rota) {
                    case 'alunos':
                        $arResult['data'][0] = $this->getInfosAlunosAtendimento($codigoCidade);
                        array_push($arResult['data'], $this->getInfosAlunosEscola($codigoCidade));
                        array_push($arResult['data'], $this->getInfosAlunosRota($codigoCidade));
                        array_push($arResult['data'], $this->getInfosAlunosEscolaridade($codigoCidade));
                        array_push($arResult['data'], $this->getInfosAlunosTurno($codigoCidade));
                        array_push($arResult['data'], $this->getInfosAlunosResidencia($codigoCidade));
                        array_push($arResult['data'], $this->getInfosAlunosCor($codigoCidade));
                        array_push($arResult['data'], $this->getInfosAlunosSexo($codigoCidade));
                        array_push($arResult['data'], $this->getInfosAlunosResponsavel($codigoCidade));
                        break;
                    case 'rotas':
                        break;
                    default:
                        $arResult = ['result' => false, 'messages' => "Recurso não existe!"];
                        break;
                }
                $this->populaResposta(count($arResult) >= 1 ? 200 : 404, $arResult, false);
            }
        }
    }

    private function getInfosAlunosAtendimento($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosAlunos($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Atendimento";
        $arResposta['titulo'] = "Porcentagem de Alunos Atendidos (Cadastrados no Sistema)";
        $arResposta['labels'] = ["Sem Rota Cadastrada", "Com Rota Cadastrada"];
        $arResposta['values'] = [$arDados['alunos_sem_rota'],$arDados['alunos_com_rota']];
        return $arResposta;
    }

    private function getInfosAlunosEscola($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosEscolas($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Escolas";
        $arResposta['titulo'] = "Numéro Médio de Alunos por Escola";
        $arResposta['labels'] =  ["Numéro Médio de Alunos Transportados por Escola"];
        $arResposta['values'] = [$arDados['alunos_transportados_escola']];
        return $arResposta;
    }

    private function getInfosAlunosRota($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosRotas($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Rotas";
        $arResposta['titulo'] = "Numéro Médio de Alunos por Rota";
        $arResposta['labels'] =  ["Numéro Médio de Alunos Transportados por Rota"];
        $arResposta['values'] = [$arDados['alunos_por_rota']];
        return $arResposta;
    }

    private function getInfosAlunosEscolaridade($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosEscolaridade($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Nível de Escolaridade";
        $arResposta['titulo'] = "Distribuição de Alunos por Nível de Escolaridade";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        
        return $arResposta;
    }

    private function getInfosAlunosTurno($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosTurno($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Turno de Aula";
        $arResposta['titulo'] = "Distribuição de Alunos por Turno de Ensino";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    private function getInfosAlunosResidencia($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosResidiencia($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Área de Residência";
        $arResposta['titulo'] = "Porcentagem de Alunos por Localização";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    private function getInfosAlunosCor($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosCor($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Cor";
        $arResposta['titulo'] = "Porcentagem de Alunos por Cor/Raça";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    private function getInfosAlunosSexo($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosSexo($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Sexo";
        $arResposta['titulo'] = "Porcentagem de Alunos por Sexo";
        $arResposta['labels'] = $arDados['labels'];
        $arResposta['values'] = $arDados['values'];
        return $arResposta;
    }

    private function getInfosAlunosResponsavel($codigoCidade){
        $modelGraficos = new GraficosModel();
        $arDados = $modelGraficos->getDadosResponsavel($codigoCidade);
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta['nome'] = "Responsável";
        $arResposta['titulo'] = "Porcentagem de Alunos por Categoria de Responsável";
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
    public function fetchAll($params = [])
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
