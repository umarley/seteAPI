<?php

namespace Application\Utils\Decorator;

use Application\Utils\UrlHelper;

class FooBar {
    
    const ACTION_INSERT = 'inserir';
    const ACTION_UPDATE = 'alterar';

    private $urlHelper;
    private $_urlAcao;
    private $_ActionAtual;
    private $idFormulario;
    private $_destinoPost;
    private $validate;
    private $idRegistro;
    private $servicoID;
    private $temPermissaoAlterar;
    private $temPermissaoInserir;
    private $htmlPersonalizado = '';
    private $htmlPersonalizadoEsquerdo = '';
    private $disableSubmit = false;
    private $superUsuario = false;
    private $redirecionarDetalhar = false;
    private $redirecionarAlterar = false;

    public function __construct($action) {

        $this->urlHelper = new UrlHelper();
        $this->_ActionAtual = explode("?", $this->urlHelper->getAction());
        $this->_urlAcao = str_replace(['/inserir', '/alterar'], "", $this->urlHelper->getRota());
        $this->_ActionAtual[0] = $action;
        
        $session = new \Laminas\Session\Container('Auth');
        
        
        if($this->_ActionAtual[0] === 'inserir' || $this->_ActionAtual[0] === 'salvar'){
            $this->_destinoPost = $this->_urlAcao . '/salvar';
            $this->validate = self::ACTION_INSERT;
        }else{
            $this->_destinoPost = $this->_urlAcao . '/atualizar';
            $this->validate = self::ACTION_UPDATE;
        }
        
    }
    
    public function setUrlAction($action){
        $this->_ActionAtual[0] = $action;
        return $this;
    }
    
    public function setRedirecionarDetalhar($bool){
        $this->redirecionarDetalhar = $bool;
        return $this;
    }
    
    public function setRedirecionarAlterar($bool){
        $this->redirecionarAlterar = $bool;
        return $this;
    }
    
    public function setBotaoPersonalizado($html){
        $this->htmlPersonalizado = $html;
        return $this;
    }
    
    public function setBotaoPersonalizadoBandoEsquerdo($html){
        $this->htmlPersonalizadoEsquerdo = $html;
        return $this;
    }
    
    public function desativarSubmit(){
        $this->disableSubmit = true;
        return $this;
    }
    
    public function setIdRegistro($valor){
        $this->idRegistro = $valor;
        return $this;
    }

    public function render() {
        echo $this->getTool();
    }
    
    public function setIdFormulario($valor){
        $this->idFormulario = $valor;
        return $this;
    }
    
    private function getTool() {
        $html = '<div class="input-field col-sm-12 col-md-12 d-flex flex-row justify-content-end">';
        $html .= '<br /><a href="'. $this->_urlAcao .'" id="btCancelar" class="waves-effect waves-light btn-submit-form btn btn-danger"><i class="material-icons">cancel</i> Cancelar</a>';

         if((($this->_ActionAtual[0] === 'inserir') || ($this->_ActionAtual[0] === 'alterar')) || ($this->superUsuario && !$this->disableSubmit)){
                   $html .= '<a href="javascript:;" id="btSubmit" class="waves-effect waves-light btn-submit-form btn btn-primary"><i class="material-icons">save</i> Salvar</a>';
         }
                  
                $html .= '</div>';      
        $html .= $this->gerarScript();
        return $html;
    }
    
    private function gerarScript(){
        $html = '<script>'
                . '
                    $(function(){ 
                    
                    $("#btSubmit").click(function(){
        
                    FuncoesGerais.RemoveValidacaoGeral();
                    $.ajax({
                      method: "POST",
                      url: "'.$this->_urlAcao.'/validar/'.$this->validate.'",
                      data: $("#'.$this->idFormulario.'").serialize(),
                      dataType: "json",
                      beforeSend: function() {
                          FuncoesGerais.Loading()
                       }
                    })
                      .done(function( dataRes ) {
                        if(dataRes.result){
                            $.ajax({
                                method: "POST",
                                url: "'.$this->_destinoPost.'",
                                data: $("#'.$this->idFormulario.'").serialize(),
                                dataType: "json",
                                beforeSend: function() {
                                    //FuncoesGerais.Loading()
                                }
                              })
                                .done(function( data ) {
                                    //FuncoesGerais.RemoveLoading()
                                    if(data.result){
                                      FuncoesGerais.RemoveLoading();
                                      FuncoesGerais.MostraMensagemSucesso(data.message); ';
                                     
                                     if($this->redirecionarDetalhar){
                                         $html .= 'location.href = "'.$this->_urlAcao.'/detalhar/'.$this->idRegistro.'"';
                                     }else if($this->redirecionarAlterar){                                         
                                         $html .= 'location.href = "'.$this->_urlAcao.'/alterar/" + data.id';
                                     }else{
                                         $html .= 'location.href = "'.$this->_urlAcao.'"';
                                     }
                                     
                                      
                          $html .= '  }else{
                                      FuncoesGerais.RemoveLoading();
                                      FuncoesGerais.MostraMensagemErro(data.messages);
                                      
                                    }
                              });
                 
                        }else{
                            FuncoesGerais.RemoveLoading();  
                             $.each(dataRes.messages, function(index, value) { 
                                FuncoesGerais.AplicaValidacao($(\'#\' + index), value[0]);
                              });  
                        }
                      
                      });
                      
                         
                       

                    });

                '
                . '});
                '
                . '</script>';
        
        return $html;
    }

}
