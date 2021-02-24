<?php

namespace Application\Utils\Decorator;

use Application\Utils\UrlHelper;

class Toolbar {

    private $_urlAcao;
    private $arOperacoes;
    private $dbSirPermissao;
    private $session;
    private $caminhoPatch;
    private $botaoPersonalizado;
    private $temPermissaoInserir;
    private $temPermissaoVisualizar;
    private $temPermissaoAlterar;
    private $temPermissaoExcluir;
    private $arPermissaoCustomizada;

    public function __construct(Array $arPermissoes) {
        $url = new UrlHelper();
        $this->session = new \Laminas\Session\Container(\Db\Core\Config::SESSION_AUTH_ADM);
        $dbCoreOperacao = new \Db\Core\Operacao();
        //$idServico = $dbCoreServico->getIdServicoByController($url->getRotaAtiva());
        $arOperacoes = $dbCoreOperacao->getOperacoesByServico($url->getRotaAtiva());
        foreach ($arOperacoes as $row){
            $this->arOperacoes[] = $row['id_operacao'];
        }
        $this->_urlAcao = $url->getRota();
        $this->temPermissaoInserir = $arPermissoes['temPermissaoInserir'];
        $this->temPermissaoVisualizar = $arPermissoes['temPermissaoVisualizar'];
        $this->temPermissaoAlterar = $arPermissoes['temPermissaoAlterar'];
        $this->temPermissaoExcluir = $arPermissoes['temPermissaoExcluir'];
        $this->arPermissaoCustomizada = $arPermissoes['arPermissoesCustomizadas'];
        $host = $url->getHost();

        $parteRota = str_replace(['http://'.$host.'/', 'https://'.$host.'/'], "", $this->_urlAcao);
        $partsExplodeRota = explode("/", $parteRota);
        $caminhoPatch = $_SERVER['DOCUMENT_ROOT'];
        $caminhoPatch = str_replace("public", "module", $caminhoPatch) . DIRECTORY_SEPARATOR . ucfirst($partsExplodeRota[0]) . DIRECTORY_SEPARATOR . "view/";
        $caminhoPatch = explode("?", $caminhoPatch . $parteRota); 
                
        $this->caminhoPatch = $caminhoPatch[0] . "/pesquisar.phtml";
        //var_dump($this->caminhoPatch);
    }

    public function render() {
        echo $this->getTool();
    }

    public function setBotaoPersonalizado($valor) {
        $this->botaoPersonalizado = $valor;
        return $this;
    }

    private function getTool() {
        $html = '<div class="col-md-12 btns-action-list">';
        if (in_array(\Db\Core\Operacao::INSERIR, $this->arOperacoes) &&  ($this->temPermissaoInserir || $this->session->arUsuario['is_administrador'] === \Db\Core\Config::SIM)) {
            $html .= '<a href="' . $this->_urlAcao . '/inserir" class="waves-effect waves-light mr-2 btn btn-action-page btn-adicionar"><i class="material-icons">add</i> Adicionar</a>';
        }
        if (in_array(\Db\Core\Operacao::DETALHAR, $this->arOperacoes) &&  ($this->temPermissaoVisualizar || $this->session->arUsuario['is_administrador'] === \Db\Core\Config::SIM)) {
            $html .= '<button type="button" onclick="FuncoesGerais.ToolbarAction(\'visualizar\',\'' . $this->_urlAcao . '/detalhar\')" class="waves-effect waves-light mr-2 btn btn-action-page btn-visualizar"><i class="material-icons">remove_red_eye</i> Visualizar</button>';
        }
        if (($this->temPermissaoAlterar || $this->temPermissaoExcluir) || $this->session->arUsuario['is_administrador'] === \Db\Core\Config::SIM) {
            $html .= '<div class="dropdown">
                        <button type="button" class="waves-effect waves-light mr-2 btn btn-secondary btn-action-page orange-background" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-toggle-second="tooltip" data-placement="top" title="Mais Ações"><i class="material-icons">more_vert</i> Outras Ações <i class="material-icons"> arrow_drop_down </i></button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMoreActions">';
            if (in_array(\Db\Core\Operacao::ALTERAR, $this->arOperacoes) &&  ($this->temPermissaoAlterar || $this->session->arUsuario['is_administrador'] === \Db\Core\Config::SIM)) {
                $html .= '<a class="dropdown-item btn-toolbar-editar" onclick="FuncoesGerais.ToolbarAction(\'alterar\',\'' . $this->_urlAcao . '/alterar\')"><i class="material-icons">create</i> Editar</a>';
            }
            if (in_array(\Db\Core\Operacao::REMOVER, $this->arOperacoes) &&  ($this->temPermissaoExcluir || $this->session->arUsuario['is_administrador'] === \Db\Core\Config::SIM)) {
                $html .= '<a onclick="FuncoesGerais.ToolbarAction(\'deletar\',\'' . $this->_urlAcao . '/remover\')" class="dropdown-item btn-toolbar-deletar" ><i class="material-icons">delete</i> Deletar</a>';
            }
            
            foreach ($this->arPermissaoCustomizada as $perm){
                $html .= '<a onclick="FuncoesGerais.ToolbarAction(\''.$perm['tipo'].'\',\'' . $this->_urlAcao . '/'.$perm['url'].'\')" class="dropdown-item btn-toolbar-deletar" ><i class="material-icons">'.$perm['icone'].'</i> '.$perm['titulo'].'</a>';
            }


            $html .= '</div>
                    </div>';
        }

        if (file_exists($this->caminhoPatch)) {
            $html .= '<button type="button" class="waves-effect waves-light btn btn-action-page btn-filtro-avancado" type="button" data-toggle="collapse" data-target="#collapseFiltroAvancado" aria-expanded="false" aria-controls="collapseFiltroAvancado"><i class="material-icons">filter_list</i> Filtro Avançado</button>

                </div>
                <div class="col-md-12 collapse" id="collapseFiltroAvancado">
                <form name="frmBusca" action="' . $this->_urlAcao .'/pesquisar" method=\"get\">
                    <div class="card card-body">';

                    ob_start(); // inicia o buffer de memória
                    include($this->caminhoPatch);
                    $html .= ob_get_contents(); // guarda o conteúdo do arquivo na variável (parseado normal).
                    ob_end_clean();

            $html .= '<hr/>
                        <div class="row">
                            <div class="col-md-12 d-flex flex-row align-items-center justify-content-end">
                                <button type="button" class="waves-effect waves-light btn btn-action-page btn-cancelar" data-toggle="collapse" data-target="#collapseFiltroAvancado" aria-expanded="false" aria-controls="collapseFiltroAvancado">Cancelar</button>
                                <button type="submit" class="waves-effect waves-light ml-2 btn btn-action-page btn-visualizar">Pesquisar</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>';
        } else {
            $html .= "</div>";
        }

        return $html;
    }

}
