<?php

class Breamcub{


    private $operacoes;
    private $_controller;
    private $_method;
    private $CI;
    private $arModulo;
    private $arFuncionalidade;
    private $_arLabelsBreamcub = ['novo' => ['icon' => 'glyphicon-plus-sign', 'label' => 'Novo'],
                                  'salvar' => ['icon' => 'glyphicon-plus-sign', 'label' => 'Novo'],
                                  'novoCliente' => ['icon' => 'glyphicon-plus-sign', 'label' => 'Novo'],
                                  'alterar' => ['icon' => 'glyphicon-edit', 'label' => 'Editar'],
                                  'atualizar' => ['icon' => 'glyphicon-edit', 'label' => 'Editar'],
                                  'index' => ['icon' => 'glyphicon-list', 'label' => 'Listar'],
                                  'salvarPagamento' => ['icon' => 'glyphicon-list', 'label' => 'Listar'],
                                  'addHistorico' => ['icon' => 'glyphicon-plus-sign', 'label' => 'Novo'],
                                  'detalhar' => ['icon' => 'glyphicon-search', 'label' => 'Detalhar'],
                                  'novoanexo' => ['icon' => 'glyphicon-plus-sign', 'label' => 'Novo Anexo'],
                                  'find' => ['icon' => 'glyphicon-search', 'label' => 'Procurar']];
    private static $funcionalidade;
    private $idRegistroEntidade;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->_controller = $this->CI->router->fetch_class();
        $this->_method = $this->CI->router->fetch_method();

        $modulo = new Db\Modulo();
        $idModulo = $modulo->getIdModuloByController($this->_controller);
        $this->arModulo = $modulo->getModuloById($idModulo);

        $funcionalidade = new Db\Funcionalidades();
        $idFuncionalidade = $funcionalidade->getIdFuncionalidadeByController($this->_controller);
        $this->arFuncionalidade = $funcionalidade->getFuncionalidadeById($idFuncionalidade);

        self::$funcionalidade = $this->arFuncionalidade;

        $operacao = new Db\Operacoes();
        $this->operacoes = $operacao->getOperacoesByFuncionalidade($this->_controller);
    }

    public function getIcone(){
        return $this->arFuncionalidade->icone;
    }

    public function getNomeFuncionalidade(){
        return $this->arFuncionalidade->nm_funcionalidade;
    }



    public function render($id = ''){
        $this->idRegistroEntidade = $id;
        echo $this->getBre();
    }

    private function getBre(){
        if(!empty($this->idRegistroEntidade)){
            $this->arFuncionalidade->url = str_replace('{id}', $this->idRegistroEntidade, $this->arFuncionalidade->url);
        }
        $html = "<ol class=\"breadcrumb\">
                <li><a href=\"javascript:void(0); \"><i class='".$this->arModulo->icone."'></i> ". $this->arModulo->nm_modulo ."</a></li>
                <li class=\"\"><a href=\"".base_url($this->arFuncionalidade->url)." \"><i class=\"". $this->arFuncionalidade->icone ."\"></i> ".$this->arFuncionalidade->nm_funcionalidade."</a></li>
                <li class=\"active\"><i class=\"glyphicon ". $this->_arLabelsBreamcub[$this->_method]['icon'] ."\"></i> ".$this->_arLabelsBreamcub[$this->_method]['label']."</li>
            </ol>";
        return $html;
    }


}