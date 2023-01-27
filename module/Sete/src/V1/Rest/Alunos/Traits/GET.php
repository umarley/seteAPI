<?php

namespace Sete\V1\Rest\Alunos\Traits;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Application\Utils\HttpCode;
use Sete\V1\Rest\Alunos\Models\AlunosModel;

trait GET {

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
            if(isset($arParams['rota'])){
                $rota = $arParams['rota'];
            }
            if (isset($rota)) {
                $this->processarGetAlunoRota($rota, $codigoCidade, $idAluno);
            } else if ($idAluno != "" && is_numeric($idAluno)) {
                $arAluno = $modelAlunos->getById($codigoCidade, $idAluno);
                $this->populaResposta(count($arAluno) > 1 ? 200 : 404, $arAluno, false);
            } else if($idAluno == "georeferenciados"){
                $this->getAlunosLocalizados($codigoCidade);
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
                case 'rota':
                    $this->getRotaAluno($codigoCidade, $idAluno);
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
    
    private function getRotaAluno($codigoCidade, $idAluno){
        $dbRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        $arIds['id_aluno'] = $idAluno;
        $arIds['codigo_cidade'] = $codigoCidade;
        $arResposta = $dbRotaAtendeAluno->getByIdAluno($arIds);
        $this->populaResposta(200, ['data'=>$arResposta], false);
    }

    private function getAlunosLocalizados($codigoCidade){
        $dbRotaAtendeAluno = new \Db\SetePG\SeteRotaAtendeAluno();
        $arResposta = $dbRotaAtendeAluno->getAllLocatedAlunos($codigoCidade);
        $this->populaResposta(count($arResposta) > 1 ? 200 : 404, ['data'=>$arResposta], false);
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
        $this->populaResposta(200, $arResultado, false);
    }

}
