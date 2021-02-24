<?php

namespace Application\Utils\Decorator;

use Application\Utils\UrlHelper;

class Grid {

    private $action;
    private $actionForm;
    private $_lista;
    private $_colunas;
    private $primaryKey;
    private $operacoes;
    private $dbSirPermissao;
    private $usuarioLogado = '';
    private $ehSuperUsuario;
    private $idServico;
    private $colunasDataBR = "";
    private $url;
    private $modulo;
    private $urlModulo;
    private $controller;

    

    public function __construct(Array $arColunas, Array $arLista) {
        if (!isset($arColunas)) {
            throw new \Exception('O Parametro de Array contendo as colunas deve ser informado.');
        } else {
            $this->_lista = $arLista;
            $this->_colunas = $arColunas;
            $this->url = new UrlHelper();
            $this->action = $this->url->getRota();
            $dbSirOperacao = new \Db\SIR\Operacao();
            $arOperacoes = $dbSirOperacao->getOperacoesByServico($this->url->getRotaAtiva());
            foreach ($arOperacoes as $row) {
                $this->operacoes[] = $row['id_operacao'];
            }
            
            $container = new \Laminas\Session\Container('Auth');
            if (!empty($container->usuario)) {
                $this->usuarioLogado = $container->id_usuario;
                $this->ehSuperUsuario = $container->super_usuario;
            }
            
           
            $dbSirServico = new \Db\SIR\Servico();
            $this->idServico = $dbSirServico->getIdServicoByController($this->url->getRotaAtiva());
            $this->dbSirPermissao = new \Db\SIR\Permissao();
            $dbSirModulo = new \Db\SIR\Modulo();
            $modulo = str_replace($this->url->getHost(), '', $this->url->getRota());
            $parts = explode("/", $modulo);
            $modulo = $parts[3];
            $this->urlModulo = $modulo;
            $this->modulo = $dbSirModulo->getNameSpaceByUrl($modulo);
            
            
            $this->controller = $this->url->getRotaAtiva();
            $this->actionForm = $this->url->getRota($this->url->getRotaAtiva()) . "/pesquisar";
        }
    }

    public function render() {
        echo $this->getTable();
    }

    public function setColunasDataBR($arColunas) {
        $this->colunasDataBR = $arColunas;
        return $this;
    }

    public function getTable() {
        $substituir = "module" . DIRECTORY_SEPARATOR . $this->modulo . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . $this->urlModulo . DIRECTORY_SEPARATOR . $this->controller . DIRECTORY_SEPARATOR . "pesquisar.phtml";
        $path = str_replace("public", $substituir, str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']));
        $html = '<div class="card card-list">
                <div id="card-grid" class="card-content">
                    <div id="filter-cotent-grid" class="row">
                        <div ID="box-filter-grid" class="input-field col s12 m4 l4 xl4">
                            <input id="filter-grid" type="text" data-jplist-control="textbox-filter" data-group="group1" data-path=".content-item" />
                            <label for="filter-grid">Pesquisar</label>
                        </div>
                        <div class="col s12 m8 l8 xl8 right right-align">';
                        
                if (@in_array(\Db\SIR\Operacao::INSERIR, $this->operacoes)) {
                   if ($this->dbSirPermissao->usuarioTemPermissaoServico($this->usuarioLogado, $this->idServico, \Db\SIR\Operacao::INSERIR) || $this->ehSuperUsuario) {
                         $html .= '<a href="'. $this->action .'/inserir" class="btn-floating btn-large waves-effect waves-light bg-azul tooltipped" data-position="top" data-tooltip="Adicionar Novo"><i class="material-icons">add</i></a>';           
                
                   }
                }
                if (@in_array(\Db\SIR\Operacao::IMPORTAR, $this->operacoes)) {
                    if ($this->dbSirPermissao->usuarioTemPermissaoServico($this->usuarioLogado, $this->idServico, \Db\SIR\Operacao::IMPORTAR) || $this->ehSuperUsuario) {
                        $html .= '<a href="'. $this->action .'/importar" class="btn-floating btn-large waves-effect waves-light bg-azul tooltipped" data-position="top" data-tooltip="Importar dados"><i class="material-icons">cloud_upload</i></a>';           
                    }
                }
        
                 $html .= '</div>
                    </div>
                    <div class="row">';
                if (file_exists($path)) {
                     $html .= '<form name="frmBusca" action="' . $this->actionForm . '" method="get"><ul class="collapsible collapsible-filter-grid">
                            <li>
                                <div class="collapsible-header noselect waves-effect waves-light tooltipped" data-position="top" data-tooltip="Filtros Avançados"><i class="material-icons">filter_list</i> Filtro Avançado</div>
                                <div class="collapsible-body">
                                    <div class="row">
                                        <h5>Filtros avançado</h5>';

            ob_start(); // inicia o buffer de memória
            include($path);
            $html .= ob_get_contents(); // guarda o conteúdo do arquivo na variável (parseado normal).
            ob_end_clean();

            $html .= '<div class="input-field col s12 m12 l12 xl12 right-align right">
                                            <button class="waves-effect waves-light btn bg-azul txt-branco btn-pesquisar-grid">PESQUISAR</button>
                                        </div>';
        $html .= '</div>
                                </div>
                            </li>
                        </ul></form>';
                }
                $html .= '</div>
                    <div class="row">
                        <div class="table-scroll col s12 m12 l12 xl12">
                            <table class="responsive-table highlight" data-jplist-group="group1">
                                <thead><tr>';
        foreach ($this->_colunas as $key => $coluna) {
            if (key_exists('primary_key', $coluna)) {
                $this->primaryKey = $coluna['primary_key'];
            }

            $html .= " <th>{$coluna['label']}</th>";
        }
        $html .= "  <th>Ações</th>    </tr>
                                </thead>
                                <tbody>";
        $primaryKey = $this->primaryKey;
        if (empty($primaryKey)) {
            throw new \Exception('A definição da PrimaryKey no array $arColunas esta ausente.');
            die;
        }
        foreach ($this->_lista as $registro) {
            $html .= "<tr id='" . $registro[$primaryKey] . "' data-jplist-item>";
            foreach ($this->_colunas as $key => $coluna) {
                $html .= '<td class="content-item"><span class="' . $key . '">' . $registro[$key] . '</span></td>';
            }

            $html .= "<td>";
            if (@in_array(\Db\SIR\Operacao::DETALHAR, $this->operacoes)) {
                if ($this->dbSirPermissao->usuarioTemPermissaoServico($this->usuarioLogado, $this->idServico, \Db\SIR\Operacao::DETALHAR) || $this->ehSuperUsuario) {
                    $html .= '<a href="' . $this->action . "/detalhar/" . $registro[$primaryKey] . '" class="btn-action-table waves-effect waves-blue tooltipped" data-position="top" data-tooltip="Detalhar"><i class="material-icons">info</i></a>';
                }
            }
            if (@in_array(\Db\SIR\Operacao::ALTERAR, $this->operacoes)) {
                if ($this->dbSirPermissao->usuarioTemPermissaoServico($this->usuarioLogado, $this->idServico, \Db\SIR\Operacao::ALTERAR) || $this->ehSuperUsuario) {
                    $html .= '<a href="' . $this->action . "/alterar/" . $registro[$primaryKey] . '" class="btn-action-table waves-effect waves-blue tooltipped" data-position="top" data-tooltip="Editar"><i class="material-icons">edit</i></a>';
                }
            }
            if (@in_array(\Db\SIR\Operacao::DUPLICAR_CADASTRO, $this->operacoes)) {
                if ($this->dbSirPermissao->usuarioTemPermissaoServico($this->usuarioLogado, $this->idServico, \Db\SIR\Operacao::DUPLICAR_CADASTRO) || $this->ehSuperUsuario) {
                    $html .= '<a href="' . $this->action . "/duplicar/" . $registro[$primaryKey] . '" class="btn-action-table waves-effect waves-blue tooltipped" data-position="top" data-tooltip="Duplicar cadastro"><i class="material-icons">control_point_duplicate</i></a>';
                }
            }
            if (@in_array(\Db\SIR\Operacao::CANCELAR, $this->operacoes)) {
                if ($this->dbSirPermissao->usuarioTemPermissaoServico($this->usuarioLogado, $this->idServico, \Db\SIR\Operacao::CANCELAR) || $this->ehSuperUsuario) {
                    $html .= '<a href="javascript:;" onclick="FuncoesGerais.MostraModal(\'Atenção!\', \'Deseja realmente cancelar o registro?\',\'POST\',\'' . $this->action . '/cancelar/' . $registro[$primaryKey] . '\',' . $registro[$primaryKey] . ')" id="' . $registro[$primaryKey] . '" class="btCancelar btn-action-table waves-effect waves-blue tooltipped" data-position="top" data-tooltip="Cancelar"><i class="material-icons">cancel</i></a>';
                }
            }
            if (@in_array(\Db\SIR\Operacao::REMOVER, $this->operacoes)) {
                if ($this->dbSirPermissao->usuarioTemPermissaoServico($this->usuarioLogado, $this->idServico, \Db\SIR\Operacao::REMOVER) || $this->ehSuperUsuario) {
                    $html .= '<a onclick="FuncoesGerais.MostraModal(\'Atenção!\', \'Deseja realmente excluir o registro?\',\'POST\',\'' . $this->action . '/remover/' . $registro[$primaryKey] . '\',' . $registro[$primaryKey] . ')" class="btn-action-table waves-effect waves-blue tooltipped" data-position="top" data-tooltip="Remover"><i class="material-icons">delete</i></a>';
                }
            }

            $html .= "</td> </tr>";
        }
        
        
       $html .= "<!-- Sem resultados -->
                <tr data-jplist-control=\"no-results\" data-group=\"group1\" data-name=\"no-results\">
                    <td>Nenhum resultado encontrado</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>"; 
        $html .= '</tbody>
                            </table>
                        </div>
                        <div class="col s12 m12 l12 xl12 center center-align">
                            <div class="box-pagination">
                                <div id="pagination-filter" data-jplist-control="pagination" data-group="group1" data-items-per-page="20" data-current-page="0" data-name="pagination1">
                                    <button type="button" data-type="first"><i class="material-icons">first_page</i></button>
                                    <button type="button" data-type="prev"><i class="material-icons">chevron_left</i></button>
                                    <div class="jplist-holder" data-type="pages">
                                        <button type="button" data-type="page">{pageNumber}</button>
                                    </div>
                                    <button type="button" data-type="next"><i class="material-icons">chevron_right</i></button>
                                    <button type="button" data-type="last"><i class="material-icons">last_page</i></button>
                                    <span data-type="info">Página {pageNumber} de {pagesNumber}</span>
                                    <div class="input-field jplist-select-content">
                                        <select class="jplist-select no-select2" data-type="items-per-page">
                                            <option value="5">5 por página</option>
                                            <option value="10" >10 por página</option>
                                            <option value="20" selected>20 por página</option>
                                            <option value="30">30 por página</option>
                                            <option value="0">Ver todos</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

        return $html;
    }
}
