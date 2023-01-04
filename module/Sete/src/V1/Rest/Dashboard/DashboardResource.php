<?php
namespace Sete\V1\Rest\Dashboard;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class DashboardResource extends API {
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
    public function fetch($id)
    {
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }
    private function getDadosDashboard($codigoCidade){
        $dbSeteRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        $dbSeteRotaPassaPorEscola = new \Db\SetePG\SeteRotaPassaPorEscola();
        $dbSeteVeiculos = new \Db\SetePG\SeteVeiculos();
        $dbSeteRotas = new \Db\SetePG\SeteRotas();
        $arResultado['Alunos'] = $dbSeteRotaAtendeAluno->getDadosDashboardAlunos($codigoCidade);
        $arResultado['Escolas'] = $dbSeteRotaPassaPorEscola->getDadosDashboardEscolas($codigoCidade);
        $arResultado['Veiculos'] = $dbSeteVeiculos->getDadosDashboardVeiculos($codigoCidade);
        $arResultado['Rotas'] = $dbSeteRotas->getDadosDashboardRotas($codigoCidade);
        $this->populaResposta(200, $arResultado, false);
        //$this->populaResposta(count($arResposta) > 1 ? 200 : 404, ['data'=>$arResposta], false);   
    }
    public function fetchAll($params = [])
    {
        $arParams = $this->event->getRouteMatch()->getParams();
        $dbGlbMunicipios = new \Db\SetePG\GlbMunicipios();
        $codigoCidade = $arParams['codigo_cidade'];
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro codigo_cidade deve ser informado!"], false);
        } else if (!$dbGlbMunicipios->municipioExiste($codigoCidade)) {
            $this->populaResposta(404, ['result' => false, 'messages' => "O municipio informado não existe!"], false);
        } else if (!$this->usuarioPodeAcessarCidade($codigoCidade)) {
            $this->populaResposta(403, ['result' => false, 'messages' => "Usuário sem permissão para acessar o municipio informado!"], false);
        } else {
            $this->getDadosDashboard($codigoCidade);  
            /*$idAluno = $arParams['alunos_id'];
            if(isset($arParams['rota'])){
                $rota = $arParams['rota'];
            }
            if (isset($rota)) {
                $this->processarGetAlunoRota($rota, $codigoCidade, $idAluno);
            } else if ($idAluno != "" && is_numeric($idAluno)) {
                $arAluno = $modelAlunos->getById($codigoCidade, $idAluno);
                $this->populaResposta(count($arAluno) > 1 ? 200 : 404, $arAluno, false);
            } else if($idAluno == "georeferenciados"){
                $this->getAlunosLocalizados($codigoCidade);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_aluno deve ser informado!"], false);
            }*/
        }
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
