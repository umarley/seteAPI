<?php


namespace Application\Utils\Decorator\Form;


class TextArea
{

    private $_readonly = false;
    private $_value;
    private $_minlength;
    private $_maxlenth;
    private $_placeholder;
    private $_disabled = false;
    private $_class;
    private $_name;
    private $_label;
    private $_id;
    private $_onkeypress;
    private $_required = false;
    private $rows = '10';
    private $heigth = 30;

    public function __construct($nomeId)
    {

        $this->_name = $nomeId;
        $this->_id = $nomeId;

    }

    public function setValue($valor){
        $this->_value = $valor;
        return $this;
    }
    
    public function setLabel($valor){
        $this->_label = $valor;
        return $this;
    }

    public function setHeigth($altura){
        $this->heigth = $altura;
        return $this;
    }

    public function setRows($rows){
        $this->rows = $rows;
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
        $html = "<div class=\"form-group\" id=\"div{$this->_id}\">";
        $html .= "<label>{$this->_label}</label>";
        $html .= "<textarea class=\"form-control {$this->_class}\" rows='".$this->rows."' cols='40' name='{$this->_name}' id='{$this->_id}'";
        if(!empty($this->_onkeypress)){
            $html .= " onkeypress='{$this->_onkeypress}'";
        }
        if(!empty($this->_minlength)){
            $html .= " minlength='{$this->_minlength}'";
        }
        if(!empty($this->_maxlenth)){
            $html .= " maxlength='{$this->_maxlenth}'";
        }
        if(!empty($this->_placeholder)){
            $html .= " placeholder='{$this->_placeholder}'";
        }
        if($this->_required){
            $html .= " required";
        }
        if($this->_readonly){
            $html .= " readonly";
        }
        if($this->_disabled){
            $html .= " disabled";
        }
        $html .= ">".$this->_value."</textarea></div>";
        return $html;
    }
    
    public function render(){
        echo $this->getHTML();
    }
}