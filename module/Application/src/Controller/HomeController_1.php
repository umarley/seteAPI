<?php

namespace Application\Controller;

use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Morrislaptop\Firestore\Factory;
use Kreait\Firebase\ServiceAccount;

class HomeController_1 extends AbstractActionController {

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
        /* $serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/../../../../config/SoftwareTER-4d63263deb38.json');

          $firestore = (new Factory)
          ->withServiceAccount($serviceAccount)
          ->withDatabaseUri(self::URL)
          ->createFirestore();



          $collection = $firestore->collection('/');
          $ar = $collection->document('municipios');

          $snap = $ar->snapshot();

          // Save a document
          // $user->set(['name' => 'morrislaptop', 'role' => 'developer']);

          // Get a document
          //$snap = $collection->snapshot();
          var_dump($snap); */
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/../../../../config/SoftwareTER-4d63263deb38.json');

        $firestore = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->createFirestore();

       /* $collection = $firestore->collection('users');
        $user = $collection->document('0QVhnjSqLzPWNWC3cEwSOQdlx4n2');*/
        
         $collection = $firestore->collection('data');
        $user = $collection->documents();
         
        // Save a document
        //$user->set(['name' => 'morrislaptop', 'role' => 'developer']);
        
        //var_dump($user->id);
        
      //  exit;
        
        // Get a document
        //$snap = $user->snapshot()->data();
        $snap = $user->rows(); // lista todas as linhas
        //$snap = $user->getIterator();
        
       // var_dump($snap);
      //  exit;
        $arLista = [];
        foreach ($snap as $key => $row){
            $arLista[$key] = $row->data();
        }
        var_dump($arLista[0]['id']);// morrislaptop
        
        exit;
    }

}
