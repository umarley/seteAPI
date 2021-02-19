<?php

namespace Application\Controller;

use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Google\Client;

class HomeController extends AbstractActionController {

    const URL = 'https://softwareter.firebaseio.com';
    const TOKEN = 'Fxh64QQdBq83e8O5rAxTTSVfx8eA2I4qsvXodqZD';
    const PATH = '/users';

    public function __construct() {
        
    }

    public function indexAction() {
        $arDados = [];
        return new ViewModel($arDados);
    }

    public function firebaseAction() {
        //https://github.com/googleapis/google-api-php-client
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../../../../config/SoftwareTER-4d63263deb38.json');
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope([
            "https://www.googleapis.com/auth/userinfo.email",
            "https://www.googleapis.com/auth/datastore",
            "https://www.googleapis.com/auth/firebase.database"
        ]);
        $client->authorize();
        $arAccessToken = $client->fetchAccessTokenWithAssertion();
        $accessToken = $arAccessToken['access_token'];
        //https://firebase.google.com/docs/firestore/use-rest-api?hl=pt
        $url = "https://firestore.googleapis.com/v1/projects/softwareter/databases/(default)/documents/config";
        
        $rest = new \Db\Core\Rest($url, []);
        $rest->setHeader([
            'Authorization' => "Bearer {$accessToken}"
        ]);
            
        $arResposta = json_decode($rest->get()->getResposta(), true);
        $arDocumentos = $arResposta['documents'];
        $arNomes = [];
        foreach ($arDocumentos as $row){
            $arNomes[] = $this->getNomesDocumento($row['name']); 
        }
        
        var_dump($arNomes);
        exit;
    }
    
    private function getNomesDocumento($valor){
        $partsNome = explode("/", $valor);
        $sizeOfArray = count($partsNome);
        return $partsNome[$sizeOfArray-1];
    }

}
