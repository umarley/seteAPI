<?php

//require APPPATH . 'helpers/utils_helper.php';

class Tree
{

    private $declarar;
    private $checkBox;
    private $iniciar;
    private $id;
    private $lista;
    private $listaLI;
    private $options;
    private $class;
    private $tag;
    private $nosAtivos;
    private $rotulo;
    private $onSelect;
    private $select_mode;
    private $onclick;
    private $height;

    function __construct()
    {
        $this->options = array();
        $this->checkBox = 'true';
    }

    public function render()
    {
        /** @todo necessário mudar o elses abaixo para exception */
        $html = '';
        if ($this->id) {
            if (count($this->lista) > 0 || !empty($this->listaLI)) {
                $this->iniciarOptions();
                $this->setBiblioteca();
                $this->setTree();
                $html = $this->addHidden();
                if (empty($this->listaLI)) {
                    $this->lista = Util::ObjectOrArray2Ul($this->lista, $this->id, $this->class, $this->nosAtivos);
                }
                $html .= $this->declarar;
                $html .= '<fieldset>';
                $html .= '<legend>' . $this->getRotulo() . '</legend>';
                $html .= '<div id="fieldTree">';
                if (empty($this->listaLI)) {
                    $html .= $this->lista;
                } else {
                    $html .= '<div id="tree_' . $this->id . '" class="' . $this->class . '">';
                    $html .= $this->listaLI;
                    $html .= '</div>';
                }
                $html .= '</div></fieldset>';
                $html .= $this->iniciar;
                return $html;
            } else {
                throw new Exception('Necessário estabelecer a lista que será transformada em tree');
            }
        } else {
            throw new Exception('Necessário atribuir o id da lista');
        }
    }

    private function setBiblioteca()
    {
        /** Armazena informações do */
//        if(Zend_Registry::isRegistered("ar_javascript")) {$ar = Zend_Registry::isRegistered("ar_javascript");}
//        else {$ar = array();}
//
//        $ar[] = '<script src="' . PASTA_JAVASCRIPT . '/jqueryui/dynatree/src/jquery.dynatree.js" type="text/javascript"></script>';
//        Zend_Registry::set("ar_javascript", $ar);
//        $this->declarar = '
//            <link href="' . PASTA_JAVASCRIPT . '/jqueryui/dynatree/src/skin/ui.dynatree.css" rel="stylesheet" type="text/css" id="skinSheet">
//            <script src="' . PASTA_JAVASCRIPT . '/jqueryui/dynatree/src/jquery.dynatree.js" type="text/javascript"></script>';
    }

    private function setTree()
    {
        $this->iniciar = '
                        <style type="text/css">
                            #fieldTree {
                                height: ' . $this->getHeight() . 'px;
                                padding: 0;
                                overflow: auto;
                            }
                        </style>
                        <script type="text/javascript">
                         var tree = $("#tree_' . $this->getId() . '").dynatree({
                              checkbox: ' . $this->getCheckBox() . ',
                              selectMode: ' . $this->getSelectMode() . ',
                              noLink: true,
                              autoCollapse: true,
                              minExpandLevel: 1,
                              onPostInit: function(isReloading, isError) {
                                 logMsg("onPostInit(%o, %o)", isReloading, isError);
                                 // Re-fire onActivate, so the text is update
                                 this.reactivate();
                              },
                              onActivate: function(node) {
                                $("#echoActive").text(node.data.title);
                              },
                              onCreate: function(node, nodeSpan) {
                                    $(span).click(function(e){
                                    alert(\'clicked\' + node);
                                });
                              },
                              onDeactivate: function(node) {
                                $("#echoActive").text("-");
                              },
                              activateKey: function(key) {
                                    var dtnode = (key === null) ? null : this.getNodeByKey(key);
                                    if( !dtnode ) {
                                        if( this.activeNode )
                                            this.activeNode.deactivate();
                                        this.activeNode = null;
                                        return null;
                                    }
                                    dtnode.focus();
                                    dtnode.activate();
                                    return dtnode;
                                },
                            onSelect: function(select, node) {
//                                nti.evento(' . $this->getId() . ',"onSelect",node.data.key);
                                var selKeys = $.map(node.tree.getSelectedNodes(), function(node){
                                    return node.data.key;
                                });
                                $("#' . $this->getId() . '").val(selKeys.join(";"));
                                ' . $this->getOnclick() . '
                                },

                                selectKey: function(key, select) {
                                    var dtnode = this.getNodeByKey(key);
                                    if( !dtnode )
                                        return null;
                                    dtnode.select(select);
                                    return dtnode;
                                },
                              onDblClick: function(node, event) {
                                logMsg("onDblClick(%o, %o)", node, event);
                                node.toggleExpand();
                              }
                            });
                    ' . $this->ativarNos() . '
                </script>';
    }

    private function addHidden()
    {
        return '<input id=' . $this->getId() . ' name=' . $this->getId() . ' type="hidden" />';
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function iniciarOptions()
    {
        $this->options = array(
            'persist' => 'location',
            'collapsed' => 'true');
    }

    private function serializarVetor($vetor)
    {
        $ar_texto = '';
        foreach ($vetor as $chave => $valor) {
            $ar_texto[] = $chave . ': ' . $valor;
        }
        return implode(',', $ar_texto);
    }

    public function setLista($lista)
    {
        $this->lista = $lista;
    }

    public function getLista()
    {
        return $this->lista;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($class)
    {
        $this->tag = $class;
    }

    public function getRotulo()
    {
        return $this->rotulo;
    }

    public function setRotulo($rotulo)
    {
        $this->rotulo = $rotulo;
    }

    public function setNosAtivos(array $nosAtivos)
    {
        $this->nosAtivos = $nosAtivos;
    }

    public function getNosAtivos()
    {
        return $this->nosAtivos;
    }

    public function ativarNos()
    {
        $txt = array();

        if (count($this->nosAtivos)) {
            foreach ($this->nosAtivos as $chave => $valor) {
                if (isset($valor['id'])) {
                    if (Util::checkCodigo($valor['id'])) {
                        //$txt[] = '$("#tree_' . $this->getId() . '").dynatree("getTree").activateKey("' . $valor['id'] . '");';
                        $txt[] = '$("#tree_' . $this->getId() . '").dynatree("getTree").getNodeByKey("' . $valor['id'] . '").select();';
                        //$txt[] = '$("#'.$this->getId().'-id-'.$valor['id'].'").prop("dtnode").select();';
                    }
                }
            }
        }
        return implode('', $txt);
    }

    public function getVar($var)
    {
        if (isset($this->$var)) {
            return $this->$var;
        }
    }

    public function setVar($var, $value)
    {
        $this->$var = $value;
    }

    public function setCheckBox($checkBox)
    {
        $this->checkBox = $checkBox;
    }

    public function getCheckBox()
    {
        return $this->checkBox;
    }

    public function setSelectMode($select_mode)
    {
        $this->select_mode = $select_mode;
    }

    public function getSelectMode()
    {
        if (!empty($this->select_mode)) {
            return $this->select_mode;
        } else {
            /** @todo Evitar uso de número mágico, criando uma estrutura própria para tipos de select.
             * Há três modos identificados:
             * 1) Somente um item pode ser selecionado;
             * 2) Permite múltipla escolha, mas somente os nós filhos;
             * 3) Permite múltipla escolha, marcando os nós pais; */;
            return 3;
        }
    }

    public function getOnclick()
    {
        return $this->onclick;
    }

    public function setOnclick($onclick)
    {
        $this->onclick = $onclick;
    }

    public function getHeight()
    {
        if ($this->height == null) {
            $this->height = 250;
        }
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

}
