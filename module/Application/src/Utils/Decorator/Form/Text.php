<?php

namespace Application\Utils\Decorator\Form;

class Text {

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
    private $_label;
    private $_type = "text";
    private $_datePicker = false;
    private $_parametrosDatePicker;
    private $_paramRangeDatePicker = false;
    private $_elementoDateRangeFrom;
    private $_elementoDateRangeTo;

    public function __construct($nomeId) {

        $this->_name = $nomeId;
        $this->_id = $nomeId;
    }

    public function setValue($valor) {
        $this->_value = $valor;
        return $this;
    }

    public function setDatePicker($parametros = [], $range = []) {
        $this->_datePicker = true;
        $this->_parametrosDatePicker = $parametros;
        if (!empty($range)) {
            $this->_paramRangeDatePicker = true;
            $this->_elementoDateRangeFrom = $range[0];
            $this->_elementoDateRangeTo = $range[1];
        }
        return $this;
    }

    public function setType($valor) {
        $this->_type = $valor;
        return $this;
    }

    public function setLabel($valor) {
        $this->_label = $valor;
        return $this;
    }

    public function onkeypress($function) {
        $this->_onkeypress = $function;
        return $this;
    }

    public function setClass(Array $class = []) {
        $this->_class = implode(' ', $class);
        return $this;
    }

    public function setMinLength($valor) {
        $this->_minlength = $valor;
        return $this;
    }

    public function setReadonly($bool) {
        $this->_readonly = $bool;
        return $this;
    }

    public function setDisabled($bool) {
        $this->_disabled = $bool;
        return $this;
    }

    public function setRequired($bool) {
        $this->_required = $bool;
        return $this;
    }

    public function setMaxLength($valor) {
        $this->_maxlenth = $valor;
        return $this;
    }

    public function setPlaceHolder($valor) {
        $this->_placeholder = $valor;
        return $this;
    }

    public function render() {
        echo $this->getHTML();
    }

    private function getHTML() {
        
        $html = "<label for='{$this->_id}'>{$this->_label}</label>";
        $html .= "<input type=\"{$this->_type}\" class=\"form-control input-sm {$this->_class}\" name='{$this->_name}' id='{$this->_id}'";
        if (!empty($this->_onkeypress)) {
            $html .= " onkeypress='{$this->_onkeypress}'";
        }
        if (!empty($this->_minlength)) {
            $html .= " minlength='{$this->_minlength}'";
        }
        if (!empty($this->_maxlenth)) {
            $html .= " maxlength='{$this->_maxlenth}'";
        }
        if (!empty($this->_placeholder)) {
            $html .= " placeholder='{$this->_placeholder}'";
        }
        if (!empty($this->_value)) {
            $html .= " value='{$this->_value}'";
        }
        if ($this->_readonly) {
            $html .= " readonly";
        }
        if ($this->_disabled) {
            $html .= " disabled";
        }
        $html .= "/>";
        if ($this->_datePicker) {
            $html .= "<script>";
            if ($this->_paramRangeDatePicker) {
                $html .= " $( function() {
                    var dateFormat = \"dd/mm/yy\",
                          from = $( \"#{$this->_elementoDateRangeFrom}\" )
                              .datepicker(";
                $html .= json_encode($this->_parametrosDatePicker);
                $html .= ")
                            .on( \"change\", function() {
                                            to.datepicker( \"option\", \"minDate\", getDate( this ) );
                                        }),
                          to = $( \"#{$this->_elementoDateRangeTo}\" ).datepicker(";
                $html .= json_encode($this->_parametrosDatePicker);
                $html .= ")
                          .on( \"change\", function() {
                                            from.datepicker( \"option\", \"maxDate\", getDate( this ) );
                                        });
                     
                        function getDate( element ) {
                            var date;
                            try {
                                date = $.datepicker.parseDate( dateFormat, element.value );
                            } catch( error ) {
                                date = null;
                            }
                    
                            return date;
                        }
                      } );";
            } else {

                $html .= "$( function() {
                            $('#{$this->_id}' ).datepicker( ";
                $html .= json_encode($this->_parametrosDatePicker);
                $html .= ");
                          } );";
            }


            $html .= "</script>";
        }


        return $html;
    }

}
