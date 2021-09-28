<?php
namespace Sete\V1\Rest\Garagens;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Sete\V1\API;

class GaragensResource extends API
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
                $arParams = $this->event->getRouteMatch()->getParams();
                $arData->codigo_cidade = $codigoCidade;
                $this->processarInsertGaragem($arData);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarInsertGaragem($data) {
        $modelGaragens = new GaragensModel();
        $boValidate = $modelGaragens->validarInsert($data);
        if ($boValidate['result']) {
            $arResult = $modelGaragens->prepareInsert($data);
            $this->populaResposta($arResult['result'] ? 201 : 400, $arResult, false);
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
    public function delete($id) {
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idGaragem= $arParams['garagens_id'];
        $this->processarRequestDELETE($codigoCidade, $idGaragem);
    }
    private function processarRequestDELETE($codigoCidade, $idGaragem) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            $modelGaragens = new GaragensModel();
            $arResult = $modelGaragens->removerRegistroById($codigoCidade, $idGaragem);
            $this->populaResposta(200, $arResult, false);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
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
        $modelGaragem = new GaragensModel();
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
            $idGaragem = $id;
            if ($idGaragem != "" && is_numeric($idGaragem)) {
                $arGaragem = $modelGaragem->getById($codigoCidade, $idGaragem);
                $this->populaResposta(count($arGaragem) > 1 ? 200 : 404, $arGaragem, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_veiculo deve ser informado!"], false);
            }
        }
    }


    private function getVeiculosGaragem($codigoCidade, $idGaragem){
        $dbSeteEscolaTemAluno = new \Db\SetePG\SeteGaragemTemVeiculo();
        $arResult = $dbSeteEscolaTemAluno->getVeiculosByGaragem($codigoCidade, $idGaragem);
        $this->populaResposta(count($arResult) > 1 ? 200 : 404, $arResult);
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
            $this->obterTodasGaragensCidade($codigoCidade);
        }
    }

    private function obterTodasGaragensCidade($codigoCidade) {
        $modelGaragens = new GaragensModel();
        $arGaragens = $modelGaragens->getAll($codigoCidade);
        $this->populaResposta(200, $arGaragens);
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
    public function update($id, $data) {
        $modelGaragens = new GaragensModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idGaragem = $arParams['garagens_id'];
        $boValidate = $modelGaragens->validarUpdate($data);
        if (empty($codigoCidade) || $idGaragem == "") {
            return ['result' => false, 'messages' => "O ID garagem e código da cidade devem ser informados!"];
        } else if ($boValidate['result']) {
            return $modelGaragens->prepareUpdate($codigoCidade, $idGaragem, $data);
        } else {
            return $boValidate;
        }
    }
}
