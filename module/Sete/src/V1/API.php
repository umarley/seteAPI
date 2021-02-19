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
                if (!$valido){
                    header('Content-Type: application/json', true, 401);
                    echo json_encode(['result' => false, 'messages' => 'Access Token inválido!']);
                    exit;
                }
            } else {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['result' => false, 'messages' => 'Cabeçalho Authorization vazio!']);
            }
        } else {
                header('Content-Type: application/json', true, 400);
            echo json_encode(['result' => false, 'messages' => 'Cabeçalho Authorization ausente!']);
        }
    }

    public function populaResposta($codigoStatus, $arResposta) {
        header('Content-Type: application/json', true, $codigoStatus);
        $arResult['data'] = $arResposta;
        $arResult['total'] = count($arResposta);
        echo json_encode($arResult);
        exit;
    }

}
