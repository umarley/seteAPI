<?php

namespace Sete\V1;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;

class API extends AbstractResourceListener {
    
    protected $_model;
    
    public function __construct() {
        
    }
    
    public function populaResposta($codigoStatus, $arResposta){
        header('Content-Type: application/json', true, $codigoStatus);
        return $arResposta;
    }
    
}
