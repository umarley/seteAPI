<?php

namespace Sete\V1\Rest\Alunos;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;
use Sete\V1\Rest\Alunos\Models\AlunosModel;

class AlunosResource extends API {

    
    use \Sete\V1\Rest\Alunos\Traits\POST;
    use \Sete\V1\Rest\Alunos\Traits\DELETE;
    use \Sete\V1\Rest\Alunos\Traits\GET;
    
    

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
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $this->processarRequestPUT($codigoCidade, $data);
    }

    private function processarRequestPUT($codigoCidade, $arData) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arData->codigo_cidade = $codigoCidade;
            $this->processarUpdateAluno($arData);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarUpdateAluno($data) {
        $modelAlunos = new AlunosModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $boValidate = $modelAlunos->validarUpdate($data, $idAluno);
        if (empty($codigoCidade) || $idAluno == "") {
            $this->populaResposta(400, ['result' => false, 'messages' => "O ID aluno e código da cidade devem ser informados!"], false);
        } else if ($boValidate['result']) {
            $this->populaResposta(200, $modelAlunos->prepareUpdate($codigoCidade, $idAluno, $data), false);
        } else {
            $this->populaResposta(400, $boValidate, false);
        }
    }

}
