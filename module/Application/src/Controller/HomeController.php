<?php
namespace Application\Controller;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;

class HomeController extends AbstractActionController
{
    
    public function __construct() {
       
    }
    
    public function indexAction()
    {
        $arDados = [];
        return new ViewModel($arDados);
    }

}
