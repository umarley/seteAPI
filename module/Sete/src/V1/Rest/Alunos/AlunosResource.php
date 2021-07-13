<?php

namespace Sete\V1\Rest\Alunos;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class AlunosResource extends AbstractResourceListener {

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data) {
        $modelAlunos = new AlunosModel();
        $boValidate = $modelAlunos->validarInsert($data);
        if ($boValidate['result']) {
            return $modelAlunos->prepareInsert($data);
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
    public function delete($id) {
        $modelAlunos = new AlunosModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $arResult = $modelAlunos->removerRegistroById($codigoCidade, $idAluno);
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
        $modelAlunos = new AlunosModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        var_dump($arParams);
        exit;
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        return $modelAlunos->getById($codigoCidade, $idAluno);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = []) {
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        var_dump($arParams);
        exit;
        $codigoCidade = $arParams['codigo_cidade'];
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            return ['result' => false, 'messages' => "O parâmetro codigo_cidade deve ser informado!"];
        } else {
            $modelAlunos = new AlunosModel();
            $arAlunos = $modelAlunos->getAll($codigoCidade);
            $arResultado['data'] = $arAlunos;
            $arResultado['total'] = count($arAlunos);
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
        $modelAlunos = new AlunosModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $boValidate = $modelAlunos->validarUpdate($data);
        if (empty($codigoCidade) || $idAluno == "") {
            return ['result' => false, 'messages' => "O ID aluno e código da cidade devem ser informados!"];
        } else if ($boValidate['result']) {
            return $modelAlunos->prepareUpdate($codigoCidade, $idAluno, $data);
        } else {
            return $boValidate;
        }
    }

}
