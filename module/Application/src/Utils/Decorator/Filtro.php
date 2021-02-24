<?php

namespace Application\Utils\Decorator;

use Application\Utils\UrlHelper;

define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', dirname(__FILE__) . DS);

class Filtro {

    private $_campos;
    private $action;
    private $modulo;
    private $url;
    private $controller;

    public function __construct(Array $arCampos) {

        $dbSirModulo = new \Db\SIR\Modulo();
        $this->_campos = $arCampos;
        $url = new UrlHelper();
        $this->action = $url->getRota($url->getRotaAtiva()) . "/pesquisar";
        $this->controller = $url->getRotaAtiva();
        $modulo = str_replace($url->getHost(), '', $url->getRota());
        $parts = explode("/", $modulo);
        //$modulo = str_replace($url->getRotaAtiva(), '', $modulo);
        $modulo = $parts[3];
        
        $this->url = $modulo;
        $this->modulo = $dbSirModulo->getNameSpaceByUrl($modulo);
    }

    public function render() {
        echo $this->getFind();
    }

    private function getFind() {

        $html = '<div class="card shadow mb-4">
                    <div class="card-header py-3">
                      <h6 class="m-0 font-weight-bold text-primary">Filtro</h6>
                    </div>
                    <div class="card-body">
                            <form name="frmBusca" action="' . $this->action . '" method=\"get\">
                                <div class="panel-body">';
        $path = str_replace("public" . DIRECTORY_SEPARATOR, "module" . DIRECTORY_SEPARATOR . $this->modulo . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . $this->url . DIRECTORY_SEPARATOR . $this->controller . DIRECTORY_SEPARATOR . "pesquisar.phtml", str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']));      
        ob_start(); // inicia o buffer de memória
        include($path);
        $html .= ob_get_contents(); // guarda o conteúdo do arquivo na variável (parseado normal).
        ob_end_clean();

        $html .= '
                                        <div class="col-md-2 col-md-offset-10 text-right">
                                            <button type="submit" class="btn btn-primary" ><i class="fa fa-search"></i> Procurar </button>
                                        </div>
                            </form>
                            </div>
                    </div>
                </div>';

        return $html;
    }

}
