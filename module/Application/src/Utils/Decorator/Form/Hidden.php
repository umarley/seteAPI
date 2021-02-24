<?php

namespace Application\Utils\Decorator\Form;


class Hidden
{

    private $_id;
    private $_nome;
    private $_value;

    public function __construct($nome)
    {
        $this->_nome = $nome;
        $this->_id = $nome;
    }

    public function setValue($valor){
        $this->_value = $valor;
        return $this;
    }

    private function getHtml()
    {
        $html = "";
        $html .= "<input type=\"hidden\" name=\"{$this->_nome}\" id='{$this->_id}' value=\"{$this->_value}\"/>";
        return $html;

    }

    public function render(){
        echo $this->getHtml();
    }

}