<?php

namespace Db\Core;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Message;
use Laminas\Cache\StorageFactory;

class System extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sir_modulo';
        $this->primaryKey = 'id_modulo';
        parent::__construct(self::DATABASE_CORE);
    }

    public function getItensMenuSistema() {
        $session = new \Laminas\Session\Container('Auth');
        $arModulos = $this->getModulosSistema($session->id_usuario);
        $arServicos = $this->getItemNivel1($arModulos, $session->id_usuario);
        //$arMenuDoisNiveis = $this->getItemNivel2($arServicos);
        //$arMenuCompleto = $this->getItemNivel3($arMenuDoisNiveis);        
        return $arServicos;
    }

    public function criaCacheAdapter($ttl = 3600) {
        $cache = StorageFactory::factory([
                    'adapter' => [
                        'name' => 'filesystem', // tipo de cache.
                        'options' => [
                            'ttl' => $ttl, // tempo que a informação ficará mantida no cache 1 semana
                            'cache_dir' => './data/cache' // diretório onde será armazenado o arquivo de cache
                        ],
                    ],
        ]);
        return $cache;
    }
    
    public function getInfoCacheSystem(){
        $arDadosCache = [];
        $diretorio = scandir('./data/cache');
        $arDadosCache['numFiles'] = count($diretorio) - 2;
        
        return $arDadosCache;
    }
    
    public function removerCache(){
        $cacheIterator = new \Zend\Cache\Storage\Adapter\Filesystem([
                            'cache_dir' => './data/cache' // diretório onde será armazenado o arquivo de cache
                        ]);
       $cacheIterator->flush();        
    }

    private function getModulosSistema($usuario) {
        $arMenu = [];
        if (!empty($usuario)) {
            $sql = "SELECT DISTINCT  sys_modulos.*
                  FROM  sys_modulos        
                  INNER JOIN  sys_servico srv ON srv.id_modulo = sys_modulos.id_modulo AND srv.display_menu = 'S'"
                    . " WHERE sys_modulos.is_ativo = 'S' AND sys_modulos.display_menu = 'S'"
                    . " AND srv.id_servico IN (SELECT DISTINCT id_servico FROM sys_grupo_permissao"
                    . " WHERE id_grupo IN (SELECT id_grupo FROM sys_grupo_usuario WHERE id_usuario = {$usuario}))"
                    . " ORDER BY ordem ASC";
            $statement = $this->AdapterBD->createStatement($sql);
            $statement->prepare();
            $result = $statement->execute();


            $this->getResultSet($result);
            foreach ($this->resultSet as $row) {
                $arMenu[] = $row;
            }
        }
        return $arMenu;
    }

    private function getItemNivel1($arMenu, $usuario) {
        // Alimenta o array com o submenu
        foreach ($arMenu as $key => $item) {
            $sql = "SELECT 
                        *
                    FROM
                        sys_servico
                    WHERE
                        is_ativo = 'S' AND servico_pai IS NULL
                            AND display_menu = 'S'
                            AND id_modulo = {$item['id_modulo']}
                            AND id_servico IN (SELECT DISTINCT
                                id_servico
                            FROM
                                sys_grupo_permissao
                            WHERE
                                id_grupo IN (SELECT 
                                        id_grupo
                                    FROM
                                        sys_grupo_usuario
                                    WHERE
                                        id_usuario = {$usuario}))
                    ORDER BY nm_servico ASC";
            $statement = $this->AdapterBD->createStatement($sql);
            $statement->prepare();
            $result = $statement->execute();
            $this->getResultSet($result);
            foreach ($this->resultSet as $row) {
                $arMenu[$key]['subMenu'][] = $row;
            }
        }
        return $arMenu;
    }

    private function getItemNivel2($arMenu) {
        //alimeta o array com submenu 2ยบ nivel
        foreach ($arMenu as $key => $subMenuNivel2) {
            if (key_exists('subMenu', $subMenuNivel2)) {
                foreach ($subMenuNivel2['subMenu'] as $index => $nivel3) {
                    $sql = "SELECT * FROM sir_servico WHERE is_ativo = 1 AND servico_pai = " . $nivel3->id_servico . "";
                    $statement = $this->AdapterBD->createStatement($sql);
                    $statement->prepare();
                    $result = $statement->execute();
                    $this->getResultSet($result);
                    foreach ($this->resultSet as $row) {
                        $arMenu[$key]['subMenu'][$index]['filhos'][] = $row;
                    }
                }
            }
        }
        return $arMenu;
    }

    private function getItemNivel3($arMenu) {
        foreach ($arMenu as $key => $subMenuNivel2) {
            if (key_exists('subMenu', $subMenuNivel2)) {
                foreach ($subMenuNivel2['subMenu'] as $index => $nivel3) {
                    if (key_exists('filhos', $nivel3)) {
                        foreach ($nivel3['filhos'] as $index3 => $neto) {
                            $sql = "SELECT * FROM sir_servico WHERE is_ativo = 1 AND servico_pai = " . $neto->id_servico . "";
                            $statement = $this->AdapterBD->createStatement($sql);
                            $statement->prepare();
                            $result = $statement->execute();
                            $this->getResultSet($result);
                            foreach ($this->resultSet as $row) {
                                $arMenu[$key]['subMenu'][$index]['filhos'][$index3]['netos'][] = $row;
                            }
                        }
                    }
                }
            }
        }
        return $arMenu;
    }
    
    /**
     * Método responsável por enviar emails
     * @param Array $arDadosMensagem o Array deve conter as seguintes chaves:
     * 
     * $arDadosMensagem['emailFrom']: Email origem da mensagem \r\n
     * $arDadosMensagem['emailTo']: Email do destinatário qual o email será enviado \r\n
     * $arDadosMensagem['emailAssunto']: Assunto do Email a ser enviado
     * $arDadosMensagem['emailMensagem']: Mensagem do Email a ser enviado
     * 
     */
    public function enviarEmail($arDadosMensagem) {
        $dbConfig = new \Db\Core\Config();

        $transport = new SmtpTransport();
        $options = new SmtpOptions([
            'name' => $dbConfig->getConfig($dbConfig::PSERVER_SMTP),
            'host' => $dbConfig->getConfig($dbConfig::PSERVER_SMTP),
            'port' => $dbConfig->getConfig($dbConfig::PPORTA_SMTP),
            'connection_class' => 'plain',
            'connection_config' => [
                'username' => $dbConfig->getConfig($dbConfig::PUSER_SMTP),
                'password' => $dbConfig->getConfig($dbConfig::PSENHA_SMTP),
                'ssl' => 'tls',
            ],
        ]);
        $transport->setOptions($options);
        $message = new Message();
        $message->addFrom($arDadosMensagem['emailFrom'], $arDadosMensagem['tituloRemetente']);
        $message->addTo($arDadosMensagem['emailTo']);
        $message->setSubject($arDadosMensagem['emailAssunto']);

        $bodyMessage = "{$arDadosMensagem['emailMensagem']} \r\n";
        

        $message->setBody($bodyMessage);
        $message->setSender($arDadosMensagem['emailTo']);
        $transport->send($message);
    }

}
