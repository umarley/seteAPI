<?php

namespace Application\Utils\Decorator\Form;


class BoxFK
{
    
    const TIPOFIELDTEXTO = 'texto';
    const TIPOFIELDSELECT = 'select';
    const TIPOFIELDHIDDEN = 'hidden';

    private $_colunas;
    private $_lista;
    private $_campos;
    private $_idPai;
    private $_oid;
    private $_titulo;
    private $_urlAction;
    private $_urlActionUpdate;
    private $_urlDelete;
    private $_urlDetalhar;
    private $_fieldFilhoFK;
    private $baseUrl;
    private $_formularioInserir;
    private $_formularioAlterar;
    private $idFormulario;
    private $larguraDialog = '600';
    private $modulo;
    private $urlModulo;
    private $_tituloBotaoAdd;
    private $_temFuncaoAlterar = false;


    public function __construct($colunas = [], $lista = [], $camposFormFK = [])
    {
        $this->_colunas = $colunas;
        $this->_lista = $lista;
        $this->_campos = $camposFormFK;
        $this->_oid = $this->gerarOid();
        
        $this->baseUrl = new \Application\Utils\UrlHelper();
        
        $dbSirModulo = new \Db\SIR\Modulo();
        $modulo = str_replace($this->baseUrl->getHost(), '', $this->baseUrl->getRota());
        $parts = explode("/", $modulo);
        $modulo = $parts[3];
        $this->urlModulo = $modulo;
        $this->modulo = $dbSirModulo->getNameSpaceByUrl($modulo);
        
    }
    
    public function setLarguraDialog($largura){
        $this->larguraDialog = $largura;
        return $this;
    }
    
    public function temFuncaoAlterar($temFuncaoAlterar){
        $this->_temFuncaoAlterar = $temFuncaoAlterar;
        return $this;
    }
    
    public function setUrlDetalharDados($url){
        $this->_urlDetalhar = $url;
        return $this;
    }
    
    public function setFieldFilhoFK($fieldFK){
        $this->_fieldFilhoFK = $fieldFK;
        return $this;
    }
    
    public function setTituloBtnAdd($value)
    {
        $this->_tituloBotaoAdd = $value;
        return $this;
    }
    

    public function setTitulo($value)
    {
        $this->_titulo = $value;
        return $this;
    }
    
    public function setIdFormulario($id){
        $this->idFormulario = $id;
        return $this;
    }
    
    public function setTelaInserir($form){
        $this->_formularioInserir = $form;
        return $this;
    }
    
    public function setTelaAlterar($form){
        $this->_formularioAlterar = $form;
        return $this;
    }

    public function setAction($action){
        $this->_urlAction = $action;
        return $this;
    }
    
    public function setActionUpdate($action){
        $this->_urlActionUpdate = $action;
        return $this;
    }
    
    public function setUrlDelete($action){
        $this->_urlDelete = $action;
        return $this;
    }

    public function setIdPai($value){
        $this->_idPai = $value;
        return $this;
    }


    private function gerarOid()
    {
        return rand(111111111, 999999999);
    }

    public function render()
    {
        echo $this->getHTML();
    }

    private function getHTML()
    {
        $html = $this->getScript();
        $html .= $this->gridFK();
        return $html;
    }
    
    private function getScript(){
        
        $js = "<script>";
        $js .= "var GridFK{$this->_oid} = (function() {

            var add = function() {
                //CRIA E MONTA A MODAL COM O FORM
                const idModal = FuncoesGerais.RandomHash(8)
                $('body').append(`
                    <div id=\"overlay-custom-{$this->_oid}\" class=\"overlay-custom-modal-home\" onclick=\"$('#overlay-custom-{$this->_oid}').remove(); $('.modal-prox-parada-home-{$this->_oid}').remove()\"></div>
                        <div id=\"modal-edit-prox-ordens\" class=\"modal-content-edit modal-prox-parada-home-{$this->_oid}\">
                            <div class=\"modal-content-header\">
                                <h4>Adicionar {$this->_titulo}</h4>
                            </div>
                            <div class=\"modal-content-content\">";
                 
                                
                            $substituir = "module" . DIRECTORY_SEPARATOR . $this->modulo . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . $this->urlModulo . DIRECTORY_SEPARATOR . "{$this->_formularioInserir}.phtml";
                            $path = str_replace("public", $substituir, str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']));    
                            ob_start(); // inicia o buffer de memória
                            include($path);
                            $js .= ob_get_contents(); // guarda o conteúdo do arquivo na variável (parseado normal).
                            ob_end_clean();   
                                
                                
        $js .= "            </div>
                            <div class=\"modal-footer\">";
        
        
                           
                               $js .= "<a onclick=\"$('#overlay-custom-{$this->_oid}').remove(); $('.modal-prox-parada-home-{$this->_oid}').remove()\" class=\"modal-close waves-effect waves-light btn-flat modal-btn-cancelar\">Cancelar</a>";
                           
                                
                            $js .=    "<a onclick=\"GridFK{$this->_oid}.EnviaAdicionar('.modal-prox-parada-home-{$this->_oid}','#overlay-custom-{$this->_oid}')\" id=\"btn-add-edit-modal-produtos{$this->_oid}\" class=\"modal-close waves-effect waves-light btn-flat modal-btn-confirmar\">Adicionar</a>
                            </div>
                        </div>
                `);";

                 foreach ($this->_campos as $value){
                    if($value['type'] === self::TIPOFIELDSELECT){
                        $js .= "  $('select#{$value['field']}').select2(); ";
                    }
                 }
                            
             $js .= "   //CARREGA SELECT2 E CORRIGE LAYOUT
                //$('select').select2()
                $('#select2insidemodal-paradas-produto').select2({
                    dropdownParent: $(`.modal-prox-parada-home-{$this->_oid}.moda-content-content`)
                })
            }

            var send = function(idOverlay, idModal) {
                //GUARDA O ID DO ITEM E A CAPACIDADE NOMINAL
                

                //ENVIA OS DADOS
                FuncoesGerais.MostraLoading()
                axios.post(`{$this->baseUrl->baseUrl()}{$this->urlModulo}/{$this->_urlAction}`, $('#form-fk{$this->_oid}').serialize())
                    .then(function(response) {
                        //console.log(response)
                        if (response.data.result) {

                            //FECHA MODAL E OVERLAY
                            $(idModal).remove()
                            $(idOverlay).remove()

                            //MOSTRA MSGEM SUCESSO E REMOVE LOADING
                            FuncoesGerais.MostraMensagemSucesso(\"O {$this->_titulo} foi adicionado com sucesso\")
                            FuncoesGerais.RemoveLoading()

                            //ATUALIZA A GRIDFK COM O NOVO VALOR INSERIDO
                            FuncoesGerais.MostraLoading()
                            axios({
                                method: 'GET',
                                url: '{$this->baseUrl->baseUrl()}{$this->urlModulo}/{$this->_urlAction}-retorno?id_pai=".$this->_idPai."&id_filho=' + response.data.id,
                              }).then(response => {
                                    let dados = response.data
                                    //console.log(dados)
                                    jplist.resetContent(() => {";
                                       
                                        $addLinhaTabelaGrid = "<tr data-jplist-item=\"\" class=\"'+dados.{$this->_fieldFilhoFK}+'\">";
                                        
                                        foreach ($this->_colunas as $value){
                                            $addLinhaTabelaGrid .= "<td id=\"{$value['field']}\" class=\"content-item\"><span>'+dados.{$value['field']}+'</span></td>";
                                        }
                                        
                                        $addLinhaTabelaGrid .= "<td>";
                                        if($this->_temFuncaoAlterar){
                                            $addLinhaTabelaGrid .= "<button onclick=\"GridFK{$this->_oid}.Atualizar('+dados.{$this->_fieldFilhoFK}+')\" type=\"button\" class=\"btn-action-table waves-effect waves-blue tooltipped\" data-position=\"top\" data-tooltip=\"Editar\"><i class=\"material-icons\">edit</i></button>";
                                        }
                                        
                                        $addLinhaTabelaGrid .= "<button onclick=\"GridFK{$this->_oid}.Remover('+dados.{$this->_fieldFilhoFK}+')\" type=\"button\" class=\"btn-action-table waves-effect waves-blue tooltipped\" data-position=\"top\" data-tooltip=\"Remover\"><i class=\"material-icons\">delete</i></button></td>";
                                        $addLinhaTabelaGrid .= "</tr>";
                             $js .= "$('#tbl-{$this->_oid} tbody').append('" . $addLinhaTabelaGrid . "')";           
                                        //$('#tbl-{$this->_oid} tbody').append('<tr data-jplist-item=\"\" class=\"1\"> <td id=\"id_usuario\" class=\"content-item\"><span>'+dados.id_usuario+'</span></td> <td id=\"usuario\" class=\"content-item\"><span>'+dados.nome+'</span></td> <td id=\"nm_usuario\" class=\"content-item\"><span>'+dados.usuario+'</span></td> <td><button onclick=\"GridFK377378039.Atualizar(2)\" type=\"button\" class=\"btn-action-table waves-effect waves-blue tooltipped\" data-position=\"top\" data-tooltip=\"Editar\"><i class=\"material-icons\">edit</i></button><button onclick=\"GridFK377378039.Remover(2)\" type=\"button\" class=\"btn-action-table waves-effect waves-blue tooltipped\" data-position=\"top\" data-tooltip=\"Remover\"><i class=\"material-icons\">delete</i></button></td> </tr>')
                             $js .=  " })
                                    
                                    FuncoesGerais.RemoveLoading()
                                })
                                .catch(error => {
                                    FuncoesGerais.RemoveLoading()
                                    FuncoesGerais.MostraMensagemErro(error)
                                })
                                  
                        } else {
                            console.log(response)
                            FuncoesGerais.MostraMensagemErro(response.data.messages)
                            FuncoesGerais.RemoveLoading()
                        }
                    })
                    .catch(function(error) {
                        console.log(error)
                        FuncoesGerais.MostraMensagemErro(error)
                        FuncoesGerais.RemoveLoading()
                    })
            }

            var remove = function(idItem) {
                //MONTA A MODAL PARA CONFIRMACAO DE EXCLUSAO
                const idModal = FuncoesGerais.RandomHash(8)
                $('body').append(`
                    <div id=\"overlay-custom-{$this->_oid}\" class=\"overlay-custom-modal-home\" onclick=\"$('#overlay-custom-{$this->_oid}').remove(); $('.modal-prox-parada-home-{$this->_oid}').remove()\"></div>
                        <div id=\"modal-edit-prox-ordens\" class=\"modal-content-edit modal-prox-parada-home-{$this->_oid}\">
                            <div class=\"modal-content-header\">
                                <h4>Aviso</h4>
                            </div>
                            <div class=\"moda-content-content\">
                                <p>Deseja realmente excluir este registro?</p>
                            </div>
                            <div class=\"modal-footer\">
                                <a onclick=\"$('#overlay-custom-{$this->_oid}').remove(); $('.modal-prox-parada-home-{$this->_oid}').remove()\" class=\"modal-close waves-effect waves-light btn-flat modal-btn-cancelar\">Cancelar</a>
                                <a onclick=\"GridFK{$this->_oid}.RemoveItem(`+idItem+`, '#overlay-custom-{$this->_oid}', '.modal-prox-parada-home-{$this->_oid}')\" id=\"btn-add-edit-modal-produtos\" class=\"modal-close waves-effect waves-light btn-flat modal-btn-confirmar\">Remover</a>
                            </div>
                        </div>
                `)
            }

            var deleteItem = function(idItem, idOverlay, idModal) {
                //ENVIA OS DADOS PARA DELETAR O ITEM
                FuncoesGerais.MostraLoading()
                axios.delete(`{$this->baseUrl->baseUrl()}{$this->urlModulo}/{$this->_urlDelete}?id_pai={$this->_idPai}&id_filho=`+idItem)
                    .then(response => {
                        if (response.data.result) {
                            console.log(response)
                            jplist.resetContent(function() {
                                $(`tr.`+idItem).remove()
                            })
                            $(idOverlay).remove()
                            $(idModal).remove()
                            FuncoesGerais.RemoveLoading()
                            FuncoesGerais.MostraMensagemSucesso(response.data.message)
                        } else {
                            $(idOverlay).remove()
                            $(idModal).remove()
                            FuncoesGerais.RemoveLoading()
                            console.log(response)
                            FuncoesGerais.MostraMensagemErro(response.data.message)
                        }

                    })
                    .catch(error => {
                        $(idOverlay).remove()
                        $(idModal).remove()
                        FuncoesGerais.RemoveLoading()
                        FuncoesGerais.MostraMensagemErro(error)
                    })
            }

            var refresh = function(idItem) {
                //CRIA E MONTA A MODAL COM O FORM
                //const idModal = FuncoesGerais.RandomHash(8)
                $('body').append(`
                    <div id=\"overlay-custom-{$this->_oid}\" class=\"overlay-custom-modal-home\" onclick=\"$('#overlay-custom-{$this->_oid}').remove(); $('.modal-prox-parada-home-{$this->_oid}').remove()\"></div>
                        <div id=\"modal-edit-prox-ordens\" class=\"modal-content-edit modal-prox-parada-home-{$this->_oid}\">
                            <div class=\"modal-content-header\">
                                <h4>Alterar {$this->_titulo}</h4>
                            </div>
                            <div class=\"modal-content-content\">";
                                
                          if($this->_temFuncaoAlterar){
                            $substituir = "module" . DIRECTORY_SEPARATOR . $this->modulo . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . $this->urlModulo . DIRECTORY_SEPARATOR . "{$this->_formularioAlterar}.phtml";
                            $path = str_replace("public", $substituir, str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']));    
                            ob_start(); // inicia o buffer de memória
                            include($path);
                            $js .= ob_get_contents(); // guarda o conteúdo do arquivo na variável (parseado normal).
                            ob_end_clean(); 
                          }      
                             
                                
                                
                                
               $js .= "     </div>
                            <div class=\"modal-footer\">
                                <a onclick=\"$('#overlay-custom-{$this->_oid}').remove(); $('.modal-prox-parada-home-{$this->_oid}').remove()\" class=\"modal-close waves-effect waves-light btn-flat modal-btn-cancelar\">Cancelar</a>
                                <a onclick=\"GridFK{$this->_oid}.EnviaAtualizacao('.modal-prox-parada-home-{$this->_oid}','#overlay-custom-{$this->_oid}', '+idItem+')\" id=\"btn-add-edit-modal-produtos\" class=\"modal-close waves-effect waves-light btn-flat modal-btn-confirmar\">Atualizar</a>
                            </div>
                        </div>
                `)

                //CARREGA SELECT2 E CORRIGE LAYOUT
               
                
                //CARREGA OS DADOS PARA O FORM
                FuncoesGerais.MostraLoading()
                axios.get(`{$this->baseUrl->baseUrl()}{$this->urlModulo}/{$this->_urlDetalhar}?id_filho=`+idItem+`&id_pai=`+IdParamUrl+``)
                    .then(response => {
                        const dados = response.data;";
                
                foreach ($this->_campos as $value){
                    if($value['type'] === self::TIPOFIELDSELECT){
                        $js .= "  $('select#{$value['field']}').select2(); "
                            . " FuncoesGerais.AtualizaSelectModal(dados.{$value['field']}, '{$value['field']}'); ";
                           
                    }else{
                       $js .= "  "
                            . " $('input#{$value['field']}').val(dados.{$value['field']}).next().addClass('active');"
                            . " "; 
                    }
                    
                }
                
                
                
                        
            $js .= "       /*     $('select#select2insidemodal-paradas-produto option:selected').val(dados.id_produto)*/
                       
                        FuncoesGerais.RemoveLoading()
                    })
                    .catch(error => {
                        instanceModalFK.close()
                        FuncoesGerais.RemoveLoading()
                        FuncoesGerais.MostraMensagemErro(error)
                    })
                    
                     
            }
            
            var refreshItem = function(idOverlay, idModal, idItem) {
                //VALIDA OS DADOS DO FORM
               /* if ($('#qtdFatorMult').val() == \"\") {
                    FuncoesGerais.AplicaValidacao($('#qtdFatorMult'), \"Este campo não pode ficar em branco\");
                    return;
                }*/

                //ENVIA OS DADOS PARA ATUALIZACAO
                                //ENVIA OS DADOS PARA CONSULTA
                                FuncoesGerais.MostraLoading()
                                axios.post(`{$this->baseUrl->baseUrl()}{$this->urlModulo}/{$this->_urlActionUpdate}`, $('#form-fk{$this->_oid}').serialize())
                                    .then(response => {
                                        //console.log(response)
                                        if (response.data.result) {
                                            jplist.resetContent(function() { ";
                                                
                                              foreach ($this->_colunas as $colFK){        
                                                  $js .= " "
                                                          . " $(`table#tbl-{$this->_oid} tr.`+response.data.dados.{$this->_fieldFilhoFK}+` td#{$colFK['field']} span`).text(response.data.dados.{$colFK['field']}); "
                                                          . " ";
                                              }
                                               
                                  $js .= "  })
                                            FuncoesGerais.RemoveLoading()
                                            $(idOverlay).remove()
                                            $(idModal).remove()
                                            FuncoesGerais.MostraMensagemSucesso('O {$this->_titulo} foi atualizado com sucesso')
                                        } else {
                                            FuncoesGerais.RemoveLoading()
                                            FuncoesGerais.MostraMensagemErro(response.data.messages)
                                        }
                                    })
                                    .catch(error => {
                                        console.log(error)
                                        FuncoesGerais.RemoveLoading()
                                        FuncoesGerais.MostraMensagemErro(error)
                                    })
            }

            return {
                Adicionar: add,
                EnviaAdicionar: send,
                Atualizar: refresh,
                EnviaAtualizacao: refreshItem,
                Remover: remove,
                RemoveItem: deleteItem
            }

        })(GridFK{$this->_oid} || undefined)";
        $js .= "</script>";
                                        
        return $js;
        
        
    }
    
    private function gridFK(){
        $html = "<div class=\"grid-fk-filter\">
                <div class=\"filter-data-grid-fk input-field \">
                    <input id=\"filter-grid-registros{$this->_oid}\" type=\"text\" data-jplist-control=\"textbox-filter\" data-group=\"group-{$this->_oid}\" data-path=\".content-item\" />
                    <label for=\"filter-grid-registros{$this->_oid}\">Pesquisar</label>
                </div>
                <div class=\"col s12 m8 l8 xl8 right right-align\">
                    <a onclick=\"GridFK{$this->_oid}.Adicionar()\" id=\"btn-gridfk{$this->_oid}\" class=\"btn-gridfk btn waves-effect waves-light\"><i class=\"material-icons\">add</i>{$this->_tituloBotaoAdd}</a>
                </div>
            </div>
            <div class=\"grid-content-fk\">
                <table id=\"tbl-{$this->_oid}\" class=\"responsive-table highlight tbl-grid-fk\" data-jplist-group=\"group-{$this->_oid}\">
                    <thead>";
                
                foreach($this->_colunas as $label){
                    $html .= "<th>{$label['label']}</th>";
                }
                    
        $html .=     " <th>Ações</th></thead>
                    <tbody>";
                        
            if(count($this->_lista) === 0){
                $html .= "<tr data-jplist-control=\"no-results\" data-group=\"group-{$this->_oid}\" data-name=\"no-results\">";
                foreach($this->_colunas as $key => $label){
                    
                    $html .= "<td>";
                    if($key === 0){ 
                        $html .= "Nenhum resultado encontrado!";
                    }  
                    $html .= "</td>";
                }
                $html .= "</tr>";
            }else{                
                foreach($this->_lista as $keyLista => $row){
                    $html .= "<tr data-jplist-item=\"\" class=\"{$row[$this->_fieldFilhoFK]}\">";
                    foreach($this->_colunas as $cols){
                        $html .= "<td id=\"{$cols['field']}\" class=\"content-item\">";
                        $html .= "<span>{$row[$cols['field']]}</span>";
                        $html .= "</td>";
                        
                    }
                    
                    $html .= "<td>";
                    if($this->_temFuncaoAlterar){
                        $html .= "<button onclick=\"GridFK{$this->_oid}.Atualizar({$row[$this->_fieldFilhoFK]})\" type=\"button\" class=\"btn-action-table waves-effect waves-blue tooltipped\" data-position=\"top\" data-tooltip=\"Editar\"><i class=\"material-icons\">edit</i></button>";
                    }
                    $html .= "<button onclick=\"GridFK{$this->_oid}.Remover({$row[$this->_fieldFilhoFK]})\" type=\"button\" class=\"btn-action-table waves-effect waves-blue tooltipped\" data-position=\"top\" data-tooltip=\"Remover\"><i class=\"material-icons\">delete</i></button></td>";
                    $html .= "</tr>";
                }
                
            }
                
        $html .=               "</tbody>
                </table>
            </div>
            <div class=\"col s12 m12 l12 xl12 center center-align\">
                <div class=\"box-pagination\">
                    <div id=\"pagination-filter\" data-jplist-control=\"pagination\" data-group=\"group-{$this->_oid}\" data-items-per-page=\"10\" data-current-page=\"0\" data-name=\"pagination-{$this->_oid}\">
                        <button type=\"button\" data-type=\"first\"><i class=\"material-icons\">first_page</i></button>
                        <button type=\"button\" data-type=\"prev\"><i class=\"material-icons\">chevron_left</i></button>
                        <div class=\"jplist-holder\" data-type=\"pages\">
                            <button type=\"button\" data-type=\"page\">{pageNumber}</button>
                        </div>
                        <button type=\"button\" data-type=\"next\"><i class=\"material-icons\">chevron_right</i></button>
                        <button type=\"button\" data-type=\"last\"><i class=\"material-icons\">last_page</i></button>
                        <span data-type=\"info\">Página {pageNumber} de {pagesNumber}</span>
                        <div class=\"input-field jplist-select-content\">
                            <select class=\"jplist-select no-select2\" data-type=\"items-per-page\">
                                <option value=\"10\" selected>10 por página</option>
                                <option value=\"20\">20 por página</option>
                                <option value=\"30\">30 por página</option>
                                <option value=\"0\">Ver todos</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>";
        return $html;
    }
   


}