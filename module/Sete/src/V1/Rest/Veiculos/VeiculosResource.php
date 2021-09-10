<?php
namespace Sete\V1\Rest\Veiculos;


use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\Rest\Veiculos;
use Sete\V1\API;

class VeiculosResource extends API
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
        $this->processarRequestPOST($codigoCidade, $data);
    }

    private function processarRequestPOST($codigoCidade, $arData) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arData->codigo_cidade =  $codigoCidade;
            $this->processarInsertVeiculo($arData);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }


    private function processarInsertVeiculo($arData) {
        $modelVeiculos = new VeiculosModel();
        $boValidate = $modelVeiculos->validarInsert($arData);
        if ($boValidate['result']) {
            $arResult = $modelVeiculos->prepareInsert($arData);
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
    public function fetch($id) {
        $modelVeiculos = new VeiculosModel();
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
            $idVeiculo = $arParams['veiculos_id'];
            if ($idVeiculo != "" && is_numeric($idVeiculo) ) {
                $arVeiculo = $modelVeiculos->getById($codigoCidade, $idVeiculo);
                $this->populaResposta(count($arVeiculo) > 1 ? 200 : 404, $arVeiculo, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_veiculo deve ser informado!"], false);
            } 
        }
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = []) {
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $dbGlbMunicipios = new \Db\SetePG\GlbMunicipios();
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro codigo_cidade deve ser informado!"], false);
        } else if (!$dbGlbMunicipios->municipioExiste($codigoCidade)) {
            $this->populaResposta(404, ['result' => false, 'messages' => "O municipio informado não existe!"], false);
        } else if (!$this->usuarioPodeAcessarCidade($codigoCidade)) {
            $this->populaResposta(403, ['result' => false, 'messages' => "Usuário sem permissão para acessar o municipio informado!"], false);
        } else {
            $this->obterTodosVeiculosCidade($codigoCidade);
        }
    }
    private function obterTodosVeiculosCidade($codigoCidade) {
        $modelVeiculos = new VeiculosModel();
        $arVeiculos = $modelVeiculos->getAll($codigoCidade);
        $arResultado['data'] = $arVeiculos;
        $arResultado['total'] = count($arVeiculos);
        header("Content-type: application/json");
        echo json_encode($arResultado);
        exit;
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
