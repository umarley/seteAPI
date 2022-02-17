<?php
namespace Sete\V1\Rest\Parametros;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class ParametrosResource extends API
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
        $parametroId  = $arParams['parametros_id'];
        if((!isset($codigoCidade) || empty($codigoCidade)) && (!isset($parametroId) || empty($parametroId))){
            $this->populaResposta(400, ['result' => false, 'messages' => 'Parâmetro codigo_cidade e/ou codigo_parametro obrigatório!'], false);
        }else if(!isset($data->valor) || empty($data->valor)){
            $this->populaResposta(400, ['result' => false, 'messages' => 'Parâmetro valor obrigatório!'], false);
        }else {
            $this->processarRequisicaoPOST($codigoCidade, $parametroId, $data->valor);
        }
    }
    
    private function processarRequisicaoPOST($codigoCidade, $codigoParametro, $valorParametro){
        $modelParametro = new ParametrosModel();
        $arResult = $modelParametro->gravarValorParametro($codigoCidade, $codigoParametro, $valorParametro);
        $this->populaResposta(201, $arResult, false);
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
        $arParams = $this->event->getRouteMatch()->getParams();
        if(!isset($arParams['codigo_cidade'])){
            $this->populaResposta(400, ['result' => false, 'messages' => 'Parâmetro codigo_cidade obrigatório!'], false);
        }else{
            $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($arParams['codigo_cidade']);
            if($usuarioPodeAcessarMunicipio){
                $this->processarRequisicaoGETALL($arParams['codigo_cidade']);
            }else{
                $this->populaResposta(403, ['result' => false, 'messages' => "Usuário não tem permissão pra acessar o municipio selecionado."]);
            }
        }
    }
    
    private function processarRequisicaoGETALL($codigoCidade){
        $modelParametros = new ParametrosModel();
        $arResult = $modelParametros->getAll($codigoCidade);
        $this->populaResposta(200, $arResult);
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
