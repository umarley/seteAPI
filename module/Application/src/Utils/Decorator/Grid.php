<?php

namespace Application\Utils\Decorator;

use Application\Utils\UrlHelper;

class Grid {

    private $_colunas;
    private $_lista;
    private $_oid;
    private $_primaryKey;

    public function __construct($arColunas, $arDados, $primaryKey) {
        
        $this->_oid = $this->gerarOID();
        $this->_colunas = $arColunas;
        $this->_lista = $arDados;
        $this->_primaryKey = $primaryKey;
    }
    
    private function gerarOID(){
        $rand = rand(0, 99999);
        return "dataGrid" . $rand;
    }

    public function render() {
        echo $this->getTable();
    }
    
    public function getTable() {
        $html = "<script> document.addEventListener(\"DOMContentLoaded\", function(event) {";
                $html .= "var columns = [";
                
                $aux = 1;
                foreach ($this->_colunas as $value){
                    $html .= "{ title: '" . $value['title'] . "', field: '" . $value['field'] ."' }";
                    if($aux < count($this->_colunas)){
                        $html .= ", ";
                    }
                    $aux++;
                }     
        $html .= "]";
          $html      .= "
                var data = [";
          
         
          $html .= "";
                $aux = 1;
                foreach ($this->_lista as $value){
                    $html .= "{ ";
                    $auxCol = 1;
                    foreach ($value as $key => $col){
                        $html .=  $key .": '" . $col ."' ";
                        if($auxCol < count($value)){
                            $html .= ", ";
                        }
                        $auxCol++;
                    }
                    
                    if($aux < count($this->_lista)){
                        $html .= "} , ";
                    }else{
                        $html .= "} ";
                    }
                    $aux++;
                    
                    
                }     
        $html .= "]";
          $html .= "
                DataTable.Render(\"data-grid\", columns, data, \"{$this->_primaryKey}\")"
                . ""
                . ""
                . ""
                . "}) </script> ";
        
        $html .= '
                    <div id="data-grid" class="divTable">
                        <div id="paginacao-datatable">
                            <div data-jplist-control="pagination" data-group="data-group-data-grid" data-items-per-page="50" data-current-page="0" data-name="pagination1">
                                <div data-type="info">
                                    Página {pageNumber} de {pagesNumber}
                                </div>

                                <button type="button" data-type="first"><i class="material-icons"> first_page </i></button>
                                <button type="button" data-type="prev"><i class="material-icons"> navigate_before </i></button>

                                <div class="jplist-holder" data-type="pages">
                                    <button type="button" data-type="page">{pageNumber}</button>
                                </div>

                                <button type="button" data-type="next"><i class="material-icons"> navigate_next </i></button>
                                <button type="button" data-type="last"><i class="material-icons"> last_page </i></button>

                                <div class="form-group d-inline-flex ml-3" data-type="items-per-page-dd" data-opened-class="show">
                                    <select class="form-control custom-select mr-sm-2" data-type="items-per-page">
                                        <option value="3">3 por Página</option>
                                        <option value="5">5 por Página</option>
                                        <option value="10">10 por Página</option>
                                        <option value="0">Ver Todos</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="scrolltable">
                            <table class="divInsideTable"></table>
                        </div>
                    </div>';
        
       

        return $html;
    }
}
