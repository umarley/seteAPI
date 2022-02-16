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
        $this->usuarioPodeGravar();
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
        $this->usuarioPodeGravar();
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $cpfMotorista = $arParams['motoristas_id'];
        $this->processarRequestDELETE($codigoCidade, $cpfMotorista);
    }
    
    private function processarRequestDELETE($codigoCidade, $cpfMotorista) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arParams = $this->event->getRouteMatch()->getParams();
            if (isset($arParams['rota'])) {
                $this->processarRotasDELETE($arParams);
            } else {
                $modelMotoristas = new MotoristasModel();
                $arResult = $modelMotoristas->removerRegistroById($codigoCidade, $cpfMotorista);
                $this->populaResposta(200, $arResult, false);
            }
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarRotasDELETE($arParams) {
        $codigoCidade = $arParams['codigo_cidade'];
        $cpfMotorista = $arParams['motoristas_id'];
        $rota = $arParams['rota'];
        switch ($rota) {
            case 'documentos':
                $this->removerEscolaAluno($codigoCidade, $idAluno);
                break;
        }
    }

    /*private function removerEscolaAluno($codigoCidade, $idAluno) {
        $dbSeteEscolaTemAluno = new \Db\SetePG\SeteEscolaTemAluno();
        $arIds['codigo_cidade'] = $codigoCidade;
        $arIds['id_aluno'] = $idAluno;
        $arResult = $dbSeteEscolaTemAluno->_delete($arIds);
        $this->populaResposta(200, $arResult, false);
        exit;
    }*/

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
        $modelMotoristas = new MotoristasModel();
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
            $cpfMotorista = $arParams['motoristas_id'];
            $rota = $arParams['rota'];
            if (isset($rota)) {
                $this->processarGetMotoristaRota($rota, $codigoCidade, $cpfMotorista);
            } else if ($cpfMotorista != "" && is_numeric($cpfMotorista)) {
                $arMotorista = $modelMotoristas->getById($codigoCidade, $cpfMotorista);
                $this->populaResposta(count($arMotorista) > 1 ? 200 : 404, $arMotorista, false);
            } else {
                $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro cpf_motorista deve ser informado!"], false);
            }
        }
    }

    private function processarGetMotoristaRota($rota, $codigoCidade, $cpfMotorista) {
        if ($cpfMotorista != "") {
            switch ($rota) {
                case 'rota':
                    $this->getRotasMotorista($codigoCidade, $cpfMotorista);
                    break;
                default:
                    $arResult = ['result' => false, 'messages' => "Recurso não existe!"];
                    break;
            }
            $this->populaResposta(count($arResult) > 1 ? 200 : 404, $arResult, false);
        } else {
            $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro cpf_motorista deve ser informado!"], false);
        }
    }

    private function getRotasMotorista($codigoCidade, $cpfMotorista) {
        $dbRotaDirigidaPorMotorista = new \Db\SetePG\SeteRotaDirigidaPorMotorista();
        $arIds['cpf_motorista'] = $cpfMotorista;
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta = $dbRotaDirigidaPorMotorista->getByCPFMotorista($arIds);
        $this->populaResposta(count($arResposta) > 0 ? 200 : 404, $arResposta, true);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
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
            $this->obterTodosMotoristasCidade($codigoCidade);
        }
    }
    
    private function obterTodosMotoristasCidade($codigoCidade) {
        $modelMotoristas = new MotoristasModel();
        $arMotoristas = $modelMotoristas->getAll($codigoCidade);
        $arResultado['data'] = $arMotoristas;
        $arResultado['total'] = count($arMotoristas);
        $this->populaResposta(200, $arResultado, false);
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
        $this->usuarioPodeGravar();
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $this->processarRequestPUT($codigoCidade, $data);
    }
    
    private function processarRequestPUT($codigoCidade, $arData) {
        $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
        if ($usuarioPodeAcessarMunicipio) {
            $arData->codigo_cidade = $codigoCidade;
            $this->processarUpdateMotorista($arData);
        } else {
            $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
        }
    }

    private function processarUpdateMotorista($data) {
        $modelMotoristas = new MotoristasModel();
        $arParams = $this->getEvent()->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $cpfMotorista = $arParams['motoristas_id'];
        $boValidate = $modelMotoristas->validarUpdate($data, $cpfMotorista);
        if (empty($codigoCidade) || $cpfMotorista == "") {
            $this->populaResposta(400, ['result' => false, 'messages' => "O CPF do motorista e código da cidade devem ser informados!"], false);
        } else if ($boValidate['result']) {
            $this->populaResposta(200, $modelMotoristas->prepareUpdate($codigoCidade, $cpfMotorista, $data), false);
        } else {
            $this->populaResposta(400, $boValidate, false);
        }
    }
}
