<?php
namespace Sete\V1\Rest\PermissaoFirebase;

use Laminas\ApiTools\ApiProblem\ApiProblem;

class PermissaoFirebaseResource extends \Sete\V1\API
{
    
    public function __construct() {
        parent::__construct();
        $this->_model = new \Sete\V1\Rest\PermissaoFirebase\PermissaoModel();
    }
    
    public function create($data)
    {
        $boValidate = $this->_model->validarPOST($data);
        if(!$boValidate['result']){
            $this->populaResposta(400, $boValidate, false);
        }else{
            $arResult = $this->_model->processarPermissaoFirebase($data);
            $this->populaResposta($arResult['codeHTTP'], $arResult['resposta'], false);
        }
    }

    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    public function fetch($id)
    {        
        $rota = $this->getEvent()->getParams();
        switch ($rota['id']){
            case 'usuarios-liberar':
                $pagina = (isset($_GET['pagina']) ? $_GET['pagina'] : 1);
                $busca = (isset($_GET['busca']) ? $_GET['busca'] : "");
                $arResult = $this->_model->getUsuariosLiberar($pagina, $busca);
                break;
        }
        $this->populaResposta(200, $arResult, false);
    }

    public function fetchAll($params = [])
    {                
       return new ApiProblem(405, 'The GET method has not been defined for collections');
    }

    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
