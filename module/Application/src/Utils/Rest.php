<?php

namespace Application\Utils;

use Laminas\Http\Client;

class Rest {

    const FORMATO_XML = 'xml';
    const FORMATO_JSON = 'json';

    private $_httpClient;
    private $url;
    private $params;
    private $_resposta;
    private $_header;
    private $formatoResposta;

    public function __construct($url, $parametros, $formato = self::FORMATO_JSON) {
        $this->setUrl($url);
        if (!empty($parametros)) {
            $this->setParametros($parametros);
        }
        $this->formatoResposta = $formato;
        $this->_httpClient = new Client();
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setParametros($arParam) {
        $this->params = $arParam;
    }

    public function setHeader($header) {
        $this->_header = $header;
    }

    public function setRawBody($body) {
        $this->_httpClient->setRawBody($body);
    }

    public function getResposta() {
        return $this->_resposta;
    }

    public function get() {
        if (!empty($this->params)) {
            $this->_httpClient->setParameterGet($this->params);
        }
        $this->_httpClient->setMethod('GET');
        $this->executar();
        return $this;
    }

    public function post($envioJson = false) {
        if ($envioJson) {
            $parametros = json_encode($this->params);
            $this->_httpClient->setRawBody($parametros);
        } else {
            $parametros = $this->params;
            if (!empty($parametros)) {
                $this->_httpClient->setParameterPost($parametros);
            }
        }
        $this->_httpClient->setMethod('POST');
        $this->executar();
        return $this;
    }
    
    public function put($envioJson = false) {
        if ($envioJson) {
            $parametros = json_encode($this->params);
            $this->_httpClient->setRawBody($parametros);
        } else {
            $parametros = $this->params;
            if (!empty($parametros)) {
                $this->_httpClient->setParameterPost($parametros);
            }
        }
        $this->_httpClient->setMethod('PUT');
        $this->executar();
        return $this;
    }

    private function executar() {
        $this->_httpClient->setUri($this->url);
        if (!empty($this->_header)) {
            $this->_httpClient->setHeaders($this->_header);
        }
        $response = $this->_httpClient->send();
        $this->_resposta = $response->getBody();
    }

}
