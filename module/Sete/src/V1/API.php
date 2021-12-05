<?php

namespace Sete\V1;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class API extends AbstractResourceListener {

    protected $_model;
    protected $accessToken;

    public function __construct($AuthAPI = true) {
        $headers = apache_request_headers();
        if (key_exists('Authorization', $headers)) {
            $accessToken = $headers['Authorization'];
            $this->accessToken = $accessToken;
            if (!empty($accessToken)) {
                $dbModelAuthenticator = new Rest\Authenticator\AuthenticatorModel();
                $valido = $dbModelAuthenticator->validarAccessToken($accessToken);
                if (!$valido) {
                    header('Access-Control-Allow-Origin: *');
                    header('Content-Type: application/json', true, 403);
                    echo json_encode(['result' => false, 'messages' => 'Access Token inválido!']);
                    exit;
                }
            } else {
                header('Access-Control-Allow-Origin: *');
                header('Content-Type: application/json', true, 400);
                echo json_encode(['result' => false, 'messages' => 'Cabeçalho Authorization vazio!']);
                exit;
            }
        } else {
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json', true, 400);
            echo json_encode(['result' => false, 'messages' => 'Cabeçalho Authorization ausente!']);
            exit;
        }
    }
    
    public function getAcessToken(){
        return $this->accessToken;        
    }
    
    protected function getBody(){
        $data = file_get_contents("php://input");
        return json_decode($data);
    }
    
    protected function usuarioPodeAcessarCidade($codigoCidade){
        $dbCoreAccessToken = new \Db\Core\AccessToken();
        $cidadeUsuario = $dbCoreAccessToken->getCodigoCidadeUsuarioAutenticado($this->accessToken);
        if($cidadeUsuario == $codigoCidade){
            return true;
        }else{
            return false;
        }
    }
    
    public function populaResposta($codigoStatus, $arResposta, $retornaLista = true) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept');
        header('Content-Type: application/json', true, $codigoStatus);

        if ($retornaLista) {
            $arResult['data'] = $arResposta;
            $arResult['total'] = count($arResposta);
        }else{
            $arResult = $arResposta;
        }
        
        if($codigoStatus !== 404){
            $arResult['result'] = isset($arResposta['result']) ? $arResposta['result'] : true;
        }else{
            $arResult['result'] = false;
        }

        echo json_encode($arResult);
        exit;
    }

}
