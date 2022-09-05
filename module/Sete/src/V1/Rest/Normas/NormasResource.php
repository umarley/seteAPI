<?php

namespace Sete\V1\Rest\Normas;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Sete\V1\API;

class NormasResource extends API {

    public function create($data) {
        $this->usuarioPodeGravar();
        $arParams = $this->event->getRouteMatch()->getParams();
        $codigoCidade = $arParams['codigo_cidade'];
        $this->processarRequestPOST($codigoCidade, $data);
    }

    private function processarRequestPOST($codigoCidade, $arData) {
        $boValidate = $this->validarNorma($arData, $_FILES);
        if (!$boValidate['result']) {
            $this->populaResposta(400, ['result' => $boValidate['result'], 'messages' => $boValidate['messages']], false);
        } else {
            $usuarioPodeAcessarMunicipio = $this->usuarioPodeAcessarCidade($codigoCidade);
            if ($usuarioPodeAcessarMunicipio) {
                $arData->codigo_cidade = $codigoCidade;
                $arNorma = (Array) $arData;
                $conteudoPDF = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
                $arNorma['arquivo_pdf'] = $conteudoPDF;
                $this->processarInsertNorma($arNorma);
            } else {
                $this->populaResposta(403, ['result' => false, 'messages' => 'Usuário sem permissão para acessar o municipio selecionado.'], false);
            }
        }
        exit;
    }

    private function validarNorma($arCampos, $arFile) {
        $boValidate = true;
        $arErros = [];
        $arCampos = (Array) $arCampos;
        if (!isset($arCampos['titulo']) || empty($arCampos['titulo'])) {
            $boValidate = false;
            $arErros['titulo'] = "O ttuílo deve ser informado!";
        }
        if (!isset($arCampos['id_tipo']) || empty($arCampos['id_tipo'])) {
            $boValidate = false;
            $arErros['titulo'] = "O tipo da norma deve ser informado!";
        } else {
            $dbNormasTipo = new \Db\Normas\TiposNormativo();
            if ($arCampos['id_tipo'] == 16 && empty($arCampos['outro_tipo'])) {
                $boValidate = false;
                $arErros['id_tipo'] = "Para o tipo Outro o campo especifique deve ser informado!";
            } else if (!$dbNormasTipo->tipoExiste($arCampos['id_tipo'])) {
                $boValidate = false;
                $arErros['id_tipo'] = "O tipo informado não existe!";
            }
        }
        foreach ($arCampos['id_assunto'] as $idAssunto) {
            if (empty($idAssunto)) {
                $boValidate = false;
                $arErros['id_assunto'][] = "O assunto da norma deve ser informado!";
            } else {
                $dbNormasAssunto = new \Db\Normas\AssuntosRegulamento();
                if ($idAssunto == 14 && empty($arCampos['outro_assunto'])) {
                    $boValidate = false;
                    $arErros['id_assunto'][] = "Para o assunto Outros o campo especifique deve ser informado!";
                } else if (!$dbNormasAssunto->assuntoExiste($idAssunto)) {
                    $boValidate = false;
                    $arErros['id_assunto'][] = "O assunto informado não existe!";
                }
            }
        }

        if (!isset($arCampos['tipo_veiculo']) || empty($arCampos['tipo_veiculo'])) {
            $boValidate = false;
            $arErros['tipo_veiculo'] = "O tipo do veículo deve ser informado!";
        } else {
            if (!in_array($arCampos['tipo_veiculo'], \Db\Enum\TipoVeiculo::TIPO_VEICULO)) {
                $boValidate = false;
                $arErros['id_assunto'] = "O Tipo do veículo informado não existe!";
            }
        }

        if (!isset($arFile['file']) || empty($arFile['file'])) {
            $boValidate = false;
            $arErros['file'] = "O arquivo PDF deve ser informado!";
        } else {
            $maxFileSize = ini_get('upload_max_filesize');
            if ($arFile['file']['type'] != 'application/pdf') {
                $boValidate = false;
                $arErros['file'] = "O arquivo deve ser do tipo PDF!";
            }
            if ($arFile['file']['error'] === 1) {
                $boValidate = false;
                $arErros['file'] = "O tamanho do arquivo enviado é maior que o tamanho suportado no servidor. Configuração atual do upload_max_filesize: {$maxFileSize}";
            }
            if ($arFile['file']['size'] > 5000000) {
                $boValidate = false;
                $arErros['file'] = "O tamanho mxáimo do arquivo permitido para o envio é de 5Mb ";
            }
        }

        return ['result' => $boValidate, 'messages' => $arErros];
    }

    private function processarInsertNorma($arData) {
        $dbSeteUsuarios = new \Db\SetePG\SeteUsuarios();
        $arAssuntos = $arData['id_assunto'];
        $outroAssunto = isset($arData['outro_assunto']) ? $arData['outro_assunto'] : null;
        unset($arData['id_assunto']);
        unset($arData['outro_assunto']);
        $arData['dt_criacao'] = date("Y-m-d H:i:s");
        $arData['criado_por'] = $dbSeteUsuarios->getUsuarioByAccessToken($this->getAcessToken())['email'];
        if(isset($arData['data_norma']) && !empty($arData['data_norma'])){
            $arData['data_norma'] = $this->formataDataSQL($arData['data_norma']);
        }
        $dbNormas = new \Db\Normas\Normas();
        $dbNormasAssunto = new \Db\Normas\NormasAssunto();
        $arResult = $dbNormas->_inserir($arData);
        $idNorma = $dbNormas->getUltimoIdInserido();
        foreach ($arAssuntos as $assunto){
            $outroAssuntoInsert = null;
            if($assunto == 14){
                $outroAssuntoInsert = $outroAssunto;
            }
            $dbNormasAssunto->_inserir([
                'id_norma' => $idNorma,
                'id_assunto' => $assunto,
                'outro_assunto' => $outroAssuntoInsert
            ]);
        }
        $this->populaResposta(200, $arResult, false);
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id) {
        $this->usuarioPodeGravar();
        $dbNormas = new \Db\Normas\Normas();
        $arResult = $dbNormas->_delete($id);
        $this->populaResposta(200, $arResult, false);
    }
    
    private function formataDataSQL($data){
        return implode('-', array_reverse(explode("/", $data)));
    }
    
    private function formataDataBR($data, $formato = 'dd/mm/yyyy')
    {

        $dateParts = explode(" ", $data);
        if ((!empty($dateParts[1]) && $dateParts[1] !== '00:00:00') && $formato !== 'dd/mm/yyyy') {
            return date("d/m/Y H:i", strtotime($data));
        } else {
            return date("d/m/Y", strtotime($data));
        }
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
        $dbNormas = new \Db\Normas\Normas();
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
            if (isset($arParams['rota'])) {
                switch ($arParams['rota']) {
                    case 'visualizar':
                        $this->visualizarPDF($arParams['normas_id']);
                        break;
                }
            } else {
                $idNorma = $arParams['normas_id'];
                if ($idNorma != "" && is_numeric($idNorma)) {
                    $arIds['codigo_cidade'] = $codigoCidade;
                    $arIds['id_norma'] = $idNorma;
                    $arNorma = $dbNormas->getById($arIds);
                    $arNorma['data_norma'] = $this->formataDataBR($arNorma['data_norma']);
                    $this->populaResposta(count($arNorma) > 1 ? 200 : 404, $arNorma, false);
                } else {
                    $this->populaResposta(400, ['result' => false, 'messages' => "O parâmetro id_veiculo deve ser informado!"], false);
                }
            }
        }
    }

    private function visualizarPDF($idNorma) {
        $dbNormas = new \Db\Normas\Normas();
        $pdf = $dbNormas->getConteudoPDF($idNorma);
        header('Content-type: application/pdf');
        echo base64_decode($pdf);
        exit;
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
        } else if (in_array($codigoCidade, ['assuntos', 'tipos'])) {
            $this->processarVariacaoNormas($codigoCidade);
        } else if (!$dbGlbMunicipios->municipioExiste($codigoCidade)) {
            $this->populaResposta(404, ['result' => false, 'messages' => "O municipio informado não existe!"], false);
        } else if (!$this->usuarioPodeAcessarCidade($codigoCidade)) {
            $this->populaResposta(403, ['result' => false, 'messages' => "Usuário sem permissão para acessar o municipio informado!"], false);
        } else {
            $this->obterTodasNormasCidade($codigoCidade);
        }
    }

    private function processarVariacaoNormas($variacao) {
        switch ($variacao) {
            case 'assuntos':
                $this->getAssuntosNormas();
                break;
            case 'tipos':
                $this->getTiposNormas();
                break;
        }
    }

    private function getAssuntosNormas() {
        $dbNormasAssuntos = new \Db\Normas\AssuntosRegulamento();
        $arAssuntos = $dbNormasAssuntos->getLista();
        $this->populaResposta(200, $arAssuntos);
    }

    private function getTiposNormas() {
        $dbNormasTipo = new \Db\Normas\TiposNormativo();
        $arMarcas = $dbNormasTipo->getLista();
        $this->populaResposta(200, $arMarcas);
    }

    private function obterTodasNormasCidade($codigoCidade) {
        $dbNormas = new \Db\Normas\Normas();
        $dbNormasAssunto = new \Db\Normas\NormasAssunto();
        $arNormas = $dbNormas->getLista($codigoCidade);
        foreach ($arNormas as $key => $norma){
            $arNormas[$key]['assuntos'] = $dbNormasAssunto->getAssuntoByNorma($norma['id']);
            $arNormas[$key]['data_norma'] = $this->formataDataBR($norma['data_norma']);
        }
        $arResultado['data'] = $arNormas;
        $arResultado['total'] = count($arNormas);
        $this->populaResposta(200, $arResultado, false);
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
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }

}
