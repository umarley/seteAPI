<?php
namespace Sete\V1\Rest\Escolas;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class EscolasResource extends AbstractResourceListener
{
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $modelEscolas = new EscolasModel();
        $boValidate = $modelEscolas->validarInsert($data);
        if ($boValidate['result']) {
            return $modelEscolas->prepareInsert($data);
        } else {
            return $boValidate;
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
        $modelEscolas = new EscolasModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idEscola = $arParams['escolas_id'];
        $arResult = $modelEscolas->removerRegistroById($codigoCidade, $idEscola);
        header("Content-type: application/json");
        echo json_encode($arResult);
        exit;
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
        $modelEscolas = new EscolasModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idEscola = $arParams['escolas_id'];
        return $modelEscolas->getById($codigoCidade, $idEscola);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        $codigoCidade = $_GET['codigo_cidade'];
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            return ['result' => false, 'messages' => "O parâmetro codigo_cidade deve ser informado!"];
        } else {
            $modelEscolas = new EscolasModel();
            $arEscolas = $modelEscolas->getAll($codigoCidade);
            $arResultado['data'] = $arEscolas;
            $arResultado['total'] = count($arEscolas);
            header("Content-type: application/json");
            echo json_encode($arResultado);
            exit;
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
        $modelEscolas = new EscolasModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idEscola = $arParams['escolas_id'];
        $boValidate = $modelEscolas->validarUpdate($data);
        if (empty($codigoCidade) || $idEscola == "") {
            return ['result' => false, 'messages' => "O ID escola e código da cidade devem ser informados!"];
        } else if ($boValidate['result']) {
            return $modelEscolas->prepareUpdate($codigoCidade, $idEscola, $data);
        } else {
            return $boValidate;
        }
    }
}
