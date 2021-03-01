<?php

namespace Application\Controller;

use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Google\Client;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseController extends AbstractActionController {

    private $_model;

    public function __construct() {
        $this->_model = new \Application\Model\FirebaseModel();
    }

    public function indexAction() {
        $arDados = [];
        return new ViewModel($arDados);
    }
    
    public function docConfigAction(){
        $arDocumentos = $this->_model->processarDocumentosConfig();
        
        var_dump($arDocumentos);
        exit;
    }
    
    public function docMunicipiosAction(){
        $arDocumentos = $this->_model->processarDocumentosMunicipios();
        
        var_dump($arDocumentos);
        exit;
    }
    
    public function findDocUserByEmailAction(){
        $email = $this->params()->fromQuery('email');
        
        $arDocumentos = $this->_model->procurarDocumentoUsuarioPorEmail($email);
        
        var_dump($arDocumentos);
        exit;
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

    private function getNomesDocumento($valor) {
        $partsNome = explode("/", $valor);
        $sizeOfArray = count($partsNome);
        return $partsNome[$sizeOfArray - 1];
    }

}
