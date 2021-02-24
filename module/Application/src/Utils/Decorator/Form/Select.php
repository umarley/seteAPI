<?php

namespace Application\Utils\Decorator\Form;

class Select {

    private $_values;
    private $_placeholder;
    private $_disabled = false;
    private $_class;
    private $_name;
    private $_id;
    private $_required = false;
    private $_multiple = false;
    private $_label;
    private $_selected;

    public function __construct($nomeId) {

        $this->_name = $nomeId;
        $this->_id = $nomeId;
    }

    public function setValue(Array $valor = []) {
        $this->_values = $valor;
        return $this;
    }

    public function setSelected($value) {
        $this->_selected = $value;
        return $this;
    }

    public function setClass(Array $class = []) {
        $this->_class = implode(' ', $class);
        return $this;
    }

    public function setDisabled($bool) {
        $this->_disabled = $bool;
        return $this;
    }

    public function setLabel($valor) {
        $this->_label = $valor;
        return $this;
    }

    public function setRequired($bool) {
        $this->_required = $bool;
        return $this;
    }

    public function setMultiple($bool) {
        $this->_multiple = $bool;
        $this->_name = $this->_name . '[]';
        return $this;
    }

    public function setPlaceHolder($valor) {
        $this->_placeholder = $valor;
        return $this;
    }

    public function getHTML() {
        $html = "<label>{$this->_label}</label>";
        $html .= "<select name=\"{$this->_name}\" id='{$this->_id}' class=\"browser-default select2-custom {$this->_class}\"";
        if ($this->_disabled) {
            $html .= " disabled ";
        }
        if ($this->_multiple) {
            $html .= " multiple";
        }
        $html .= ">";
        if (!empty($this->_placeholder)) {
            $html .= "<option value=\"\"> ------ {$this->_placeholder} ------ </option>";
        }
      
        foreach ($this->_values as $chave => $value) {
            $html .= "<option value='{$chave}'";
            if ($this->_multiple) {
                if (in_array($chave, $this->_selected)) {
                    $html .= 'selected >';
                } else {
                    $html .= '>';
                }
            } else {
                if (!empty($this->_selected)) {
                    if ($this->_selected == $chave) {
                        $html .= 'selected >';
                    } else {
                        $html .= '>';
                    }
                } else {
                    $html .= " >";
                }
            }


            $html .= "{$value}</option>";
        }
        $html .= "</select>";
        return $html;
    }

    public function render() {
        echo $this->getHTML();
    }

}
