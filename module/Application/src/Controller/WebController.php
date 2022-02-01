<?php

namespace Application\Controller;

use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Google\Client;
use Google\Cloud\Firestore\FirestoreClient;

class WebController extends AbstractActionController {

    private $_model;

    public function __construct() {
        //$this->_model = new \Application\Model\FirebaseModel();
    }

    public function indexAction() {
        $arDados = [];
        return new ViewModel($arDados);
    }
    
    public function RecuperarSenhaAction(){
        
        
    }

}
