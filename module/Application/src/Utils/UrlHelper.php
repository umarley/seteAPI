<?php

namespace Application\Utils;

class UrlHelper {

    private $host;
    private $uri;
    private $url;
    private $protocolo;
    private $basePath;

    public function __construct() {
        $this->host = $_SERVER['HTTP_HOST'];
        $this->protocolo = (isset($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://';
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->url = $this->protocolo . $this->host . $this->uri;
        $this->basePath = getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;  
        
        $this->forcarUsoHTTPS();
        
    }
    
    private function forcarUsoHTTPS(){
        $dbConfig = new \Db\Core\Config();
        $usoHttps = $dbConfig->getConfig($dbConfig::USAHTTPS);
        if($usoHttps === $dbConfig::SIM){
            $this->protocolo = 'https://';
        }
    }

    public function baseUrl($path = '') {
        return $this->protocolo . $this->host . "/" . $path;
    }
    
    public function basePath($path = ''){
        $slug = $this->uri . $path; //preg_replace("/([a-z0-9])\w+/", ".." , $this->uri);
        return $slug;
    }

    public function getRota($controller = '') {
        if (!empty($controller)) {
            $arPartsUri = explode("/", $this->uri);
            if (in_array($controller, $arPartsUri)) {
                foreach ($arPartsUri as $chave => $part) {
                    if ($part === $controller) {
                        $url = $this->baseUrl($arPartsUri[$chave - 1] . "/" . $part);
                    }
                }
                return $url;
            }
        } else {
            $partsRota = explode("/", $this->uri);
            if (isset($partsRota[2])) {
                $rota = $partsRota[1] . "/" . $partsRota[2];
            } else {
                $rota = $partsRota[0];
            }
            return $this->baseUrl($rota);
        }
    }

    public function getRotaAtiva() {
        $rota = "";
        $arPartsUri = explode("/", $this->uri);
        if (empty($arPartsUri[2])) {
            $rota = $arPartsUri[1];
        } else {
            if (@substr_count($arPartsUri[2], "=") > 0) {
                $rota = $arPartsUri[1];
            } else {
                $rota = $arPartsUri[2];
            }
        }
        return $rota;
    }

    public function getModulo() {
        $arPartsUri = explode("/", $this->uri);
        return ucfirst($arPartsUri[1]);
    }

    public function getAction() {
        $arPartsUri = explode("/", $this->uri);
        if (isset($arPartsUri[3])) {
            if (substr_count($arPartsUri[3], '?') === 0) {
                $action = $arPartsUri[3];
            } else {
                $partsAction = explode("?", $arPartsUri[3]);
                if($partsAction[0] === 'pesquisar'){
                    $action = 'index';
                }else{
                    $action = $partsAction[0];
                }
            }
        } else {
            $action = 'index';
        }
        return $action;
    }

    public function getParamId() {
        $arPartsUri = explode("/", $this->uri);
        if (isset($arPartsUri[4])) {
            return $arPartsUri[4];
        } else {
            return null;
        }
    }

    public function getHost() {
        return $this->host;
    }

    public function getProtocolo() {
        return $this->protocolo;
    }

}
