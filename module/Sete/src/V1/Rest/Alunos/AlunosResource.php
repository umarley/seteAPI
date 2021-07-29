<?php

namespace Sete\V1\Rest\Alunos;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class AlunosResource extends API {

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
                $this->processarInsertAluno($arData);
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
            case 'escola':
                $this->associarEscolaAluno($arDados);
                break;
        }
    }

    private function associarEscolaAluno($arDados) {
        $dbSeteEscolas = new \Db\SetePG\SeteEscolas();
        $dbSeteEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        if ($arDados->id_escola !== "") {
            if (!$dbSeteEscolas->escolaExiste($arDados->id_escola, $arDados->codigo_cidade)) {
                $this->populaResposta(404, ['result' => false, 'messages' => "Escola informada não existe!"]);
            } else if ($dbSeteEscolaTemAluno->alunoAssociadoEscola($arDados->id_aluno, $arDados->codigo_cidade)) {
                $this->populaResposta(400, ['result' => false, 'messages' => "Aluno já associado a uma escola!"], false);
            } else {
                $this->populaResposta(201, $dbSeteEscolaTemAluno->_inserir([
                            'codigo_cidade' => $arDados->codigo_cidade,
                            'id_escola' => $arDados->id_escola,
                            'id_aluno' => $arDados->id_aluno
                        ]), false);
            }
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_escola deve ser informado!"], false);
        }
    }

    private function processarInsertAluno($arData) {
        $modelAlunos = new AlunosModel();
        $boValidate = $modelAlunos->validarInsert($arData);
        if ($boValidate['result']) {
            $arResult = $modelAlunos->prepareInsert($arData);
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
    public function delete($id) {
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $this->processarRequestDELETE($codigoCidade, $idAluno);
    }

    private function processarRequestDELETE($codigoCidade, $idAluno) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            if (isset($arParams['rota'])) {
                $this->processarRotasDELETE($arParams);
            } else {
                $modelAlunos = new AlunosModel();
                $arResult = $modelAlunos->removerRegistroById($codigoCidade, $idAluno);
                $this->populaResposta(200, $arResult, false);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasDELETE($arParams) {
        $codigoCidade = $arParams['codigo_cidade'];
        $idAluno = $arParams['alunos_id'];
        $rota = $arParams['rota'];
        switch ($rota) {
            case 'escola':
                $this->removerEscolaAluno($codigoCidade, $idAluno);
                break;
        }
    }

    private function removerEscolaAluno($codigoCidade, $idAluno) {
        $dbSeteEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_aluno'] = $idAluno;
        $arResult = $dbSeteEscolaTemAluno->_delete($arIds);
        $this->populaResposta(200, $arResult, false);
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
        $dbGlbMunicipios = new \Db\SetePG\GlbMunicipios();
        $codigoCidade = $arParams['codigo_cidade'];
        if (!isset($codigoCidade) || empty($codigoCidade)) {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro codigo_cidade deve ser informado!"], false);
        } else if (!$dbGlbMunicipios->municipioExiste($codigoCidade)) {
            $this->populaResposta(404, ['result' => false, 'messages' => "O municipio informado não existe!"], false);
        } else if (!$this->usuarioPodeAcessarCidade($codigoCidade)) {
            $this->populaResposta(403, ['result' => false, 'messages' => "Usuário sem permissão para acessar o municipio informado!"], false);
        } else {
            $idAluno = $arParams['alunos_id'];
            $rota = $arParams['rota'];
            if (isset($rota)) {
                $this->processarGetAlunoRota($rota, $codigoCidade, $idAluno);
            } else if ($idAluno != "" && is_numeric($idAluno)) {
                $arAluno = $modelAlunos->getById($codigoCidade, $idAluno);
                $this->populaResposta(count($arAluno) > 1 ? 200 : 404, $arAluno, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_aluno deve ser informado!"], false);
            }
        }
    }

    private function processarGetAlunoRota($rota, $codigoCidade, $idAluno) {
        if ($idAluno != "" && is_numeric($idAluno)) {
            switch ($rota) {
                case 'escola':
                    $this->getEscolaAluno($codigoCidade, $idAluno);
                    break;
                default:
                    $arResult = ['result' => false, 'messages' => "Recurso não existe!"];
                    break;
            }
            $this->populaResposta(count($arResult) > 1 ? 200 : 404, $arResult, false);
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_aluno deve ser informado!"], false);
        }
    }
    
    private function getEscolaAluno($codigoCidade, $idAluno){
        $dbEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        $arIds['id_aluno'] = $idAluno;
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta = $dbEscolaTemAluno->getById($arIds);
        $this->populaResposta(count($arResposta) > 1 ? 200 : 404, $arResposta, false);
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
            $this->obterTodosAlunosCidade($codigoCidade);
        }
    }

    private function obterTodosAlunosCidade($codigoCidade) {
        $modelAlunos = new AlunosModel();
        $arAlunos = $modelAlunos->getAll($codigoCidade);
        $arResultado['data'] = $arAlunos;
        $arResultado['total'] = count($arAlunos);
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
