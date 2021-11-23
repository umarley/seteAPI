<?php
namespace Sete\V1\Rest\Censo;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Sete\V1\API;

class CensoResource extends API
{
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $this->processarRequisicaoPOST($codigoCidade, $data);
    }
    
    private function processarRequisicaoPOST($codigoCidade, $arData){
        $modelCenso = new \Sete\V1\Rest\Censo\CensoModel();
        $escolasValida = $modelCenso->validarEscolas($codigoCidade, $arData->escolas);
        if($escolasValida['result']){
            $alunosValido = $modelCenso->validarAlunos($arData->alunos);
            if($alunosValido['result']){
                $this->processarImportacaoCenso($codigoCidade, $arData);
            }else{
                $this->populaResposta(400, $alunosValido, false);
            }
        }else{
            $this->populaResposta(400, $escolasValida, false);
        }
    }
    
    private function processarImportacaoCenso($codigoCidade, $arData){
        $modelCenso = new \Sete\V1\Rest\Censo\CensoModel();
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $usuarioAutenticado = $dbCoreAccessToken->getEmailUsuarioSETEByAccessToken($this->getAcessToken());
        $arResult = $modelCenso->processarImportacaoEscola($arData->escolas, $usuarioAutenticado, $codigoCidade);
        if($arResult['result']){
            $arResultAlunos = $modelCenso->processarImportacaoAluno($arData->alunos, $usuarioAutenticado, $codigoCidade);
            $codigoHTTP = ($arResultAlunos['result'] ? 201 : 500);
            $this->populaResposta($codigoHTTP, $arResultAlunos, false);
        }else{
            $this->populaResposta(400, $arResult, false);
        }
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

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        return new ApiProblem(405, 'The GET method has not been defined for collections');
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
