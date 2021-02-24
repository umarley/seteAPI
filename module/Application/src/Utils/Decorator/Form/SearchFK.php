<?php
namespace Application\Utils\Decorator\Form;

class SearchFK
{

    private $_readonly = false;
    private $_value;
    private $_minlength;
    private $_maxlenth;
    private $_placeholder;
    private $_disabled = false;
    private $_class;
    private $_name;
    private $_id;
    private $_onkeypress;
    private $_required = false;
    private $_type = "text";
    private $_oid;
    private $_titulo;
    private $_urlAction = "";
    private $_label;
    private $_urlCadastro;
    private $_idCampoSubmit;
    private $_arCriteriosBuscaFK = [];
    private $_arColunasGridFK;
    private $_objValue;

    public function __construct($nomeId)
    {

        $this->_name = $nomeId;
        $this->_id = $nomeId;
        $this->_oid = $this->gerarOID();

    }

    /**
     * @param  Array $valor = ['id' => '', 'label' => ''];
     *
     */
    public function setValue(Array $valor){
        $this->_value = $valor;
        return $this;
    }
    
    public function setObjetoValue($obj){
        $this->_objValue = $obj;
        return $this;
    }

    public function setUrlAction($url){
        $this->_urlAction = $url;
        return $this;
    }
    
    public function setCriterioBuscaFK(Array $arCriterio){
        $this->_arCriteriosBuscaFK = $arCriterio;
        return $this;
    }
    
    public function setColunaGridFK(Array $arColunas){
        $this->_arColunasGridFK = $arColunas;
        return $this;
    }

    public function setUrlNovoCadastro($url){
        $this->_urlCadastro = $url;
        return $this;
    }
    
    public function setTitulo($valor){
        $this->_titulo = $valor;
        return $this;
    }

    public function setLabel($valor){
        $this->_label = $valor;
        return $this;
    }

    public function setCampoSubmit($idCampo){
        $this->_idCampoSubmit = $idCampo;
        return $this;
    }

    public function setType($valor){
        $this->_type = $valor;
        return $this;
    }

    public function onkeypress($function){
        $this->_onkeypress = $function;
        return $this;
    }

    public function setClass(Array $class = []){
        $this->_class = implode(' ', $class);
        return $this;
    }

    public function setMinLength($valor){
        $this->_minlength = $valor;
        return $this;
    }

    public function setReadonly($bool){
        $this->_readonly = $bool;
        return $this;
    }

    public function setDisabled($bool){
        $this->_disabled = $bool;
        return $this;
    }

    public function setRequired($bool){
        $this->_required = $bool;
        return $this;
    }

    public function setMaxLength($valor){
        $this->_maxlenth = $valor;
        return $this;
    }

    public function setPlaceHolder($valor){
        $this->_placeholder = $valor;
        return $this;
    }

    private function getHTML(){
       $valorSearchFk = (!empty($this->_value[1])) ? $this->_value[1] : $valorSearchFk = "";
       $valorHiddenFK = (!empty($this->_value[0])) ? $this->_value[0] : "";
        $html = "<div class=\"input-search-fk\" >";
        $html .= "<label for=\"search_{$this->_id}\">{$this->_label}</label>";
        $html .= "<input type=\"text\" class=\"form-control input-sm\" name=\"search_{$this->_name}\" id=\"search_{$this->_id}\" disabled=\"disabled\" value=\"{$valorSearchFk}\">";
        $html .= "<a class=\"waves-effect waves-light btn-search\" onclick=\"DataTable.ModalGridFK(this, columns, data)\"><i class=\"material-icons\">search</i></a>";
        $html .= "<input type=\"hidden\" name=\"{$this->_name}\" id=\"{$this->_id}\" value=\"{$valorHiddenFK}\">";
        $html .= "</div>";
        
        return $this->getScriptJS() . $html;
    }
    
    private function getScriptJS(){
        
        $urlHelper = new \Application\Utils\UrlHelper();
        $baseUrl = $urlHelper->baseUrl();
        $js = "<script>";
        $js .= "var FK = (() => {

                let chamaModal = function() {
                    //const idModal = FuncoesGerais.RandomHash(8)

                    $('div#bread-fix').before(`
                        <div id=\"overlay-custom-{$this->_oid}\" class=\"overlay-custom-modal-home\" onclick=\"$('#overlay-custom-{$this->_oid}').remove(); $('.modal-prox-parada-home-{$this->_oid}').remove()\"></div>
                        <div id=\"modal-edit-prox-ordens\" class=\"modal-content-edit modal-prox-parada-home-{$this->_oid}\">
                            <div class=\"modal-content-header\">
                                <h4>{$this->_titulo}</h4>
                            </div>
                            <div class=\"moda-content-content\">
                                <div class=\"row\" style=\"margin: 0 0 -35px 0;\">
                                    <div class=\"input-field col s12 input-field-modal-form-fk\">
                                        <div class=\"input-field input-field-fk-modal\">
                                            <select id=\"modal-criterio-fk-{$this->_oid}\" class=\"jplist-select no-select2\">
                                                <option value=\"\" disabled selected>Selecione uma opção</option>";
                                                
                                        foreach ($this->_arCriteriosBuscaFK as $key => $row){
                                            $js .= "<option value=\"{$key}\">{$row}</option>";
                                        }
                                                
        $js .= "                                    </select>
                                            <label>Critério</labe>
                                        </div>
                                        <div class=\"input-field input-search-fk-modal\" style=\"margin: 0 0 -35px 15px;\">
                                            <input type=\"text\" class=\"form-control input-sm\" id=\"modal-search-form-fk-{$this->_oid}\">
                                            <label for=\"search-form-fk-{$this->_oid}\" class=\"\">Pesquisar</label>
                                        </div>
                                        <a class=\"waves-effect waves-light\" style=\"margin-left: 5px;\" onclick=\"FK.RetornaDados('{$this->_oid}')\">Pesquisar</a>                  
                                    </div>
                                </div>
                                <hr/>
                                <div class=\"row\">
                                    <div class=\"input-field col s12\">
                                        <input id=\"fk-modal-search\" data-jplist-control=\"textbox-filter\" data-group=\"tb-modal-fk\" data-name=\"input-tb-modal-fk\" data-path=\".content-item\" type=\"text\" />
                                        <label for=\"fk-modal-search\">Filtrar Dados</label>
                                    </div>
                                    <div class=\"table-scroll col s12 m12 l12 xl12\">
                                        <table class=\"responsive-table highlight\" data-jplist-group=\"tb-modal-fk\">
                                            <thead>
                                                <tr>"; 
                                                    foreach($this->_arColunasGridFK as $row){
                                                        $js .= "<th>{$row['label']}</th>";
                                                    }
                                                    
                         $js .=                     "<th>Ação</th>   
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr data-jplist-control=\"no-results\" data-group=\"tb-modal-fk\" data-name=\"no-results\">
                                                    <td>Nenhum resultado encontrado</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class=\"col s12 m12 l12 xl12 center center-align\">
                                        <div class=\"box-pagination\">
                                            <div id=\"pagination-filter\" data-jplist-control=\"pagination\" data-group=\"group1\" data-items-per-page=\"20\" data-current-page=\"0\" data-name=\"pagination1\">
                                                <button type=\"button\" data-type=\"first\"><i class=\"material-icons\">first_page</i></button>
                                                <button type=\"button\" data-type=\"prev\"><i class=\"material-icons\">chevron_left</i></button>
                                                <button type=\"button\" data-type=\"next\"><i class=\"material-icons\">chevron_right</i></button>
                                                <button type=\"button\" data-type=\"last\"><i class=\"material-icons\">last_page</i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class=\"modal-footer\">
                                <a onclick=\"$('#overlay-custom-{$this->_oid}').remove(); $('.modal-prox-parada-home-{$this->_oid}').remove()\" class=\"modal-close waves-effect waves-light btn-flat modal-btn-cancelar\">Fechar</a>
                            </div>
                        </div>
                    `)

                    jplist.init()
                    M.AutoInit()
                }

                let retornaDados = function(idModal) {
                    let termo = $('#modal-search-form-fk').val()
                    let criterio = $('#modal-criterio-fk').val()

                    jplist.resetContent(() => {
                        $('[data-jplist-group=\"tb-modal-fk\"] tr[data-jplist-item]').remove()
                    })

                    FuncoesGerais.MostraLoading()
                    axios.get(`{$baseUrl}{$this->_urlAction}?criterio=`+$('#modal-criterio-fk-{$this->_oid}').val()+`&termo=`+$('#modal-search-form-fk-{$this->_oid}').val())
                        .then(response => {
                            let dados = response.data
                            dados.map((obj, index) => {
                                jplist.resetContent(function() {
                                    $('[data-jplist-group=\"tb-modal-fk\"] tbody').append(`
                                        <tr id=\"'+index+'\" data-jplist-item=\"\">";
                                        foreach($this->_arColunasGridFK as $row){
                                            $js .= "<td class=\"content-item\"><span class=\"codigo\">`+obj.{$row['objeto']}+`</span></td>";
                                        }    
                   $js .=  "            <td><a class=\"btn-modal-form-fk waves-effect waves-light\" onclick=\"FK.Seleciona('`+obj.{$this->_objValue}+`', `+idModal+`, `+obj.{$this->_id}+`)\">Selecionar</a></td>
                                        </tr>
                                    `)
                                })
                                return obj   
                            })

                            FuncoesGerais.RemoveLoading()
                        })
                        .catch(error => {
                            FuncoesGerais.RemoveLoading()
                            console.log(error)
                        })
                }

                let selecionaItem = function(descricao, idModal, id) {
                    $('input#search_{$this->_id}').val(descricao)
                    $(`#overlay-custom-`+idModal).remove()
                    $(`.modal-prox-parada-home-`+idModal).remove()
                    $('#{$this->_id}').attr('value',id)
                }

                return {
                    Modal: chamaModal,
                    RetornaDados: retornaDados,
                    Seleciona: selecionaItem
                }

            })(FK || undefined)";
                    
                    $js .= "</script>";
                    
        return $js;
        
        
    }
    
    public function render(){
        echo $this->getHTML();
    }
    
    private function gerarOID(){
        return rand(1000, 9999);
    }
}