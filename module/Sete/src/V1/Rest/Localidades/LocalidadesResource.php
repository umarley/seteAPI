<?php

namespace Sete\V1\Rest\Localidades;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;
use Application\Utils\HttpCode;

class LocalidadesResource extends API {

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data) {
        $this->usuarioPodeGravar();
        return new ApiProblem(405, 'The POST method has not been defined');
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id) {
        $this->usuarioPodeGravar();
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
        $arParams = $this->event->getRouteMatch()->getParams();
        $this->processarCollectionGET($arParams);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = []) {
        $arParams = $this->event->getRouteMatch()->getParams();
        $this->processarCollectionGET($arParams);
    }

    private function processarCollectionGET($arParams) {
        switch ($arParams['entidade']) {
            case 'estados':
                $this->processarEntidadeEstados($arParams);
                break;
            case 'municipios':
                $this->processarEntidadeMunicipios($arParams);
                break;
            default:
                $this->populaResposta(404, ['result' => false, 'messages' => 'Recurso não encontrado.'], false);
                break;
        }
    }

    private function processarEntidadeEstados($arParams) {
        $modelLocalidade = new LocalidadesModel();
        $arEstados = $modelLocalidade->getTodosEstados();
        $this->populaResposta(200, $arEstados);
    }

    private function processarEntidadeMunicipios($arParams) {
        if (isset($arParams['codigo_estado'])) {
            $modelLocalidade = new LocalidadesModel();
            $arMuncipios = $modelLocalidade->getTodosMunicipiosByEstado($arParams['codigo_estado']);
            $this->populaResposta(empty($arMuncipios) ? HttpCode::NOT_FOUND : HttpCode::OK, $arMuncipios);
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => 'Informe o código do municipio para continuar.']);
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
        $this->usuarioPodeGravar();
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }

}
