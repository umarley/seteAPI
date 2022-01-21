<?php
namespace Sete\V1\Rest\Custo;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class CustoResource extends API
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
    public function fetch($id)
    {
        $arParams     = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idRota       = $arParams['id_rota'];
        $this->_model = new \Sete\V1\Rest\Custo\CustoModel();
        $boValidateRotaAndCidade = $this->_model->checarRotaAndCidadeExistem($codigoCidade, $idRota);
        if($boValidateRotaAndCidade['result']){
            $this->processarValidacaoParametrosCusto($codigoCidade, $idRota);
        }else{
            $this->populaResposta($boValidateRotaAndCidade['http_code'], ['result' => $boValidateRotaAndCidade['result'], 'messages' => $boValidateRotaAndCidade['messages']], false);
        }
        exit;
    }
    
    private function processarValidacaoParametrosCusto($codigoCidade, $idRota){
        $this->_model = new \Sete\V1\Rest\Custo\CustoModel();
        $boValidate = $this->_model->validarParametrosCusto($codigoCidade, $idRota);
        
        
        
        
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
