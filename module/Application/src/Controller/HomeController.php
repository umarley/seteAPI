<?php

namespace Application\Controller;

use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Google\Client;
use Google\Cloud\Firestore\FirestoreClient;

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

    public function firebaseClientAction() {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../../../../config/autoload/google.local.json');
        $projectId = 'softwareter';

        $db = new FirestoreClient([
            'projectId' => $projectId
        ]);
        $citiesRef = $db->collection('municipios')->document('3501806')->collection('alunos');
        $documents = $citiesRef->documents();
       //var_dump($citiesRef->data());
        foreach ($documents as $row){
            var_dump($row->id());  
        }
        
        
        //var_dump($citiesRef);

        
        exit;
       // var_dump($citiesRef);
        
      /*  foreach ($citiesRef as $document) {
            var_dump($document);
        }*

        exit;
        var_dump($citiesRef);
        // echo $citiesRef->name() . "<br />";

        foreach ($citiesRef as $row) {
            echo $row->name();
        }

        // var_dump($citiesRef);
        exit;
        $snapshot = $query->documents();
        foreach ($snapshot as $document) {
            printf('Document %s returned by query state=CA' . PHP_EOL, $document->id());
        }

        exit;*/
    }

    public function firebaseRestAction() {
        //https://github.com/googleapis/google-api-php-client
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../../../../config/autoload/google.local.json');
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope([
            "https://www.googleapis.com/auth/userinfo.email",
            "https://www.googleapis.com/auth/datastore",
            "https://www.googleapis.com/auth/firebase.database"
        ]);
        $client->authorize();
        $arAccessToken = $client->fetchAccessTokenWithAssertion();

        var_dump($arAccessToken);

        $accessToken = $arAccessToken['access_token'];
        //https://firebase.google.com/docs/firestore/use-rest-api?hl=pt
        $url = "https://firestore.googleapis.com/v1/projects/softwareter/databases/(default)/documents/municipios/1101708/alunos/EDUARDO-MELO-FLITIZ-03-07-2004";

        $rest = new \Db\Core\Rest($url, []);
        $rest->setHeader([
            'Authorization' => "Bearer {$accessToken}"
        ]);

        $arResposta = json_decode($rest->get()->getResposta(), true);
        $arDocumentos = $arResposta['documents'];
        $arNomes = [];
        foreach ($arDocumentos as $row) {
            $arNomes[] = $this->getNomesDocumento($row['name']);
        }

        var_dump($arNomes);
        exit;
    }

    private function getNomesDocumento($valor) {
        $partsNome = explode("/", $valor);
        $sizeOfArray = count($partsNome);
        return $partsNome[$sizeOfArray - 1];
    }

}
