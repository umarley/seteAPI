<?php
namespace Sete\V1\Rest\Motoristas;

use Sete\V1\API;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\Rest\Motoristas\MotoristasModel;

class MotoristasResource extends API
{
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data) {
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $this->processarRequestPOST($codigoCidade, $data);
    }

    private function processarRequestPOST($codigoCidade, $arData) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            if (isset($arParams['rota'])) {
                $this->processarRotasPOST($arParams, $arData);
            } else {
                $arData->codigo_cidade = $codigoCidade;
                $this->processarInsertMotorista($arData);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasPOST($arParams, $arDados) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $rota = $arParams['rota'];
        $arDados->id_aluno = $idAluno;
        $arDados->codigo_cidade = $codigoCidade;
        switch ($rota) {
            case 'rota':
                $this->associarRotaMotorista($arDados);
                break;
        }
    }
    
    private function processarInsertMotorista($arData) {
        $modelMotoristas = new MotoristasModel();
        $boValidate = $modelMotoristas->validarInsert($arData);
        if ($boValidate['result']) {
            $arResult = $modelMotoristas->prepareInsert($arData);
            $this->populaResposta(200, $arResult, false);
        } else {
            $this->populaResposta(400, $boValidate, false);
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
