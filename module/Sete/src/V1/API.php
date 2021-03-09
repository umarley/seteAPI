<?php

namespace Sete\V1;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class API extends AbstractResourceListener {

    protected $_model;

    public function __construct() {
        $headers = apache_request_headers();
        if (key_exists('Authorization', $headers)) {
            $accessToken = $headers['Authorization'];
            if (!empty($accessToken)) {
                $dbModelAuthenticator = new Rest\Authenticator\AuthenticatorModel();
                $valido = $dbModelAuthenticator->validarAccessToken($accessToken);
                if (!$valido) {
                    header('Access-Control-Allow-Origin: *');
                    header('Content-Type: application/json', true, 401);
                    echo json_encode(['result' => false, 'messages' => 'Access Token inválido!']);
                    exit;
                }
            } else {
                header('Access-Control-Allow-Origin: *');
                header('Content-Type: application/json', true, 400);
                echo json_encode(['result' => false, 'messages' => 'Cabeçalho Authorization vazio!']);
            }
        } else {
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/json', true, 400);
            echo json_encode(['result' => false, 'messages' => 'Cabeçalho Authorization ausente!']);
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
