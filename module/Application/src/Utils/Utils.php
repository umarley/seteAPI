<?php

namespace Application\Utils;

class Utils
{

    const MINLATIN = 'á,à,â,ã,ä,é,è,ê,ë,í,ì,î,ï,ó,ò,õ,ô,ö,ú,ù,û,ü,ç,ñ,",\'';
    const MAXLATIN = 'Á,À,Â,Ã,Ä,É,È,Ê,Ë,Í,Ì,Î,Ï,Ó,Ò,Õ,Ô,Ö,Ú,Ù,Û,Ü,Ç,Ñ,",\'';
    const TRANSLATIN = 'a,a,a,a,a,e,e,e,e,i,i,i,i,o,o,o,o,o,u,u,u,u,c,n';

    /**
     * Complementa um número com zeros à esquerda retornando uma string.<br>
     */
    public static function adicionarDigitosEsquerda($totalDigitos, $numero)
    {
        $tmNumero = count($numero);
        $novoNumero = '';
        for ($i = 0; $i < ($totalDigitos - $tmNumero); $i++) {
            $novoNumero = $novoNumero . '0';
        }
        $novoNumero = $novoNumero . $numero;
        return $novoNumero;
    }

    /**
     * Identifica e retorna em dias a diferença entre duas datas informadas no padrão dd/mm/YYYY.<br>
     * Retorno igual a 0 = Datas iguais.<br>
     * Retorno menor que 0 = Data inicial maior.<br>
     * Retorno maior que 0 = Data final maior.
     */
    public static function difDatas($dt_inicial, $dt_final)
    {
        //Data inicial
        list($dia_i, $mes_i, $ano_i) = explode("/", $dt_inicial);
        //Data final
        list($dia_f, $mes_f, $ano_f) = explode("/", $dt_final);
        //Obtem tempo unix no formato timestamp
        $mk_i = mktime(0, 0, 0, $mes_i, $dia_i, $ano_i);
        //Obtem tempo unix no formato timestamp
        $mk_f = mktime(0, 0, 0, $mes_f, $dia_f, $ano_f);
        //Acha a diferença entre as datas
        $diferenca = $mk_f - $mk_i;
        $dias = (int)ceil($diferenca / (60 * 60 * 24));
        if ($dias == 0) {
            //É a mesma data
            return $dias;
        } elseif ($dias > 0) {
            //Data final é maior que data inicial
            return $dias;
        } elseif ($dias < 0) {
            //Data inicial é maior que data final
            return $dias;
        }
    }

    public static function difDatasHoras($dt_inicial, $dt_final)
    {
        //Data inicial
        list($dia_i, $mes_i, $ano_i) = explode("/", substr($dt_inicial, 0, 10));
        list($hora_i, $minuto_i) = explode(":", substr($dt_inicial, 11, 15));
        //Data final
        list($dia_f, $mes_f, $ano_f) = explode("/", substr($dt_final, 0, 10));
        list($hora_f, $minuto_f) = explode(":", substr($dt_final, 11, 15));
        //Obtem tempo unix no formato timestamp
        $mk_i = mktime($hora_i, $minuto_i, 0, $mes_i, $dia_i, $ano_i);
        //Obtem tempo unix no formato timestamp
        $mk_f = mktime($hora_f, $minuto_f, 0, $mes_f, $dia_f, $ano_f);
        //Acha a diferença entre as datas
        $diferenca = $mk_f - $mk_i;
        $dias = (int)ceil($diferenca / (60 * 60 * 24));
        if ($dias > 0) {
            //Data final é maior que data inicial
            return true;
        } elseif ($dias <= 0) {
            //Data inicial é maior que data final
            return false;
        }
    }

    public static function ObjectOrArray2Ul($lista, $id, $class)
    {
        if (is_array($lista) || is_object($lista)) {
            $ar[] = '<div id="tree_' . $id . '" class="' . $class . '">';
            $ar[] = '<ul>';
            $ar[] = self::Array2Ul($lista);
            $ar[] = '</ul></div>';
            return implode('', $ar);
        } else {
            return $lista;
        }
    }

    public static function validaChaveDeAcessoNotaFiscal($chave, $documentoEmissor)
    {
        $cnpjChave = substr($chave, 6, 14);
        if ($cnpjChave == $documentoEmissor) {
            return true;
        } else {
            return false;
        }
    }

    public static function Array2Ul($lista)
    {
        $ul = "";
        if (is_object($lista)) {
            $lista = self::objectToArray($lista);
        }

        foreach ($lista as $chave => $valor) {
            if (is_array($valor) || is_object($valor)) {
                $ul .= '<li><span class="folder">' . $chave . '</span>';
                $ul .= '<ul>';
                $ul .= self::Array2Ul($valor);
                $ul .= '</ul>';
            } else {
                if (isset($valor)) {
                    $ul .= '<li id="' . $chave . '" name="' . $chave . '"><span class="file">' . $valor . '</span>';
                } else {
                    $ul .= '<li><span class="file">' . $chave . '</span>';
                }
            }
            $ul .= '</li>';
        }
        return $ul;
    }

    public static function objectToArray($object)
    {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }
        if (is_object($object)) {
            return get_object_vars($object);
        }
    }

    /**
     * Transforma um array em stdClass
     * @param Array $array Vetor de dados
     * @return stdClass Objeto stdClass
     */
    public static function arrayToObject($array)
    {
        if (is_array($array)) {
            return (object)array_map(__METHOD__, $array);
        } else {
            return $array;
        }
    }

    public static function serializar($string)
    {
        $string = str_replace("&", CARACTERE_ECOMERCIAL, $string);
        $string = str_replace("=", CARACTERE_IGUAL, $string);
        return $string;
    }

    public static function desserializar($string)
    {
        $string = str_replace(CARACTERE_ECOMERCIAL, "&", $string);
        $string = str_replace(CARACTERE_IGUAL, "=", $string);
        return $string;
    }

    public static function db2tree($ar_registros)
    {
        //@todo ainda falta completar
        if (is_object($ar_registros)) {
            $ar_registros = self::objectToArray($ar_registros);
        }
        if (count($ar_registros)) {
            $ar_dado = $ar_registro[0];

            foreach ($ar_dado as $chave => $valor) {
                if (is_object($valor)) {
                    $ar_registros = self::objectToArray($ar_dado);
                }
                if ($valor) {

                }
            }
        }


        return $ar_registros;
    }

    /**
     * Esta função transforma um objeto DBTable, que é um array de objetos em
     * um array chave/valor. Necessário haver os campos id e nome
     */
    public static function DbTable2Array($db_table)
    {
        $ar = array();
        foreach ($db_table as $chave => $valor) {
            $ar[$valor->id] = $valor->nome;
        }
        return $ar;
    }

    /**
     * Valida se uma variável é booleana
     * @param string $valor
     * @return bool
     */
    public static function checkBool($valor)
    {
        return strtolower($valor) == 'true' || strtolower($valor) == 'false' || strtolower($valor) == '0' || strtolower($valor) == '1';
    }

    /**
     *  Valida se uma variável é inteira
     * @param mixed $valor
     * @return bool
     */
    public static function checkInteger($valor)
    {
        return is_numeric($valor);
    }

    /**
     * Verifica se é inteiro e válido
     * @param mixed $valor
     * @return bool
     */
    public static function checkCodigo($valor)
    {
        if (isset($valor)) {
            return $valor && self::checkInteger($valor) && $valor > 0;
        }
    }

    /**
     * Verifica se uma variável é numérica
     * @param mixed $valor
     * @return bool
     */
    public static function checkNumeric($valor)
    {
        /**
         * @todo verificar o separador decimal
         */
        return is_numeric($valor);
    }

    /**
     * Verifica se a variável é uma string
     * @param string $valor
     * @return string
     */
    public static function checkText($valor)
    {
        return true;
    }

    /**
     * Verifica se é uma data
     * @param string $valor
     * @return bool
     */
    public static function checkDate($valor)
    {
        $tmp = sscanf($valor, '%d/%d/%d');
        return checkdate((int)$tmp[1], (int)$tmp[0], (int)$tmp[2]);
    }

    /**
     * Verifica se o horário é valido
     * @param string $hora
     * @return bool
     */
    public static function checkTime($hora)
    {
        $tmp = sscanf($hora, '%d:%d');
        if (count($tmp) != 2) {
            return false;
        }
        return self::checkInteger($tmp[0]) && self::checkInteger($tmp[1]) && $tmp[0] >= 0 && $tmp[0] < 24 && $tmp[1] >= 0 && $tmp[1] < 60;
    }

    /**
     * Checa se variável é uma array e não está vazio.
     *
     * @param array $arDados Array de dados para teste
     * @return boolean
     */
    public static function checkArrayNaoVazio($arDados)
    {
        $boVinculo = false;
        if (!is_array($arDados)) {
            return false;
        }

        if (count($arDados)) {
            $boVinculo = true;
        }

        return $boVinculo;
    }

    /**
     * Verifica se o timestamp é valido
     * @param string $timestamp
     * @return bool
     */
    public static function checkTimestamp($timestamp)
    {
        $tmp = explode(" ", $timestamp);
        if (count($tmp) != 2) {
            return false;
        }
        return self::checkDate($tmp[0]) && Util::checkTime($tmp[1]);
    }

    /**
     * Generates an url given the name of a route.
     *
     * @access public
     *
     * @param  array $urlOptions Options passed to the assemble method of the Route object.
     * @param  mixed $name The name of a Route to use. If null it will use the current Route
     * @param  bool $reset Whether or not to reset the route defaults with those provided
     * @return string Url for the link href attribute.
     */
    public static function url(array $urlOptions = array(), $name = null, $reset = true, $encode = true)
    {
        $urlOptions = array_map('strtolower', $urlOptions);
        $module = isset($urlOptions['module']) ? $urlOptions['module'] : 'application';
        $controller = isset($urlOptions['controller']) ? $urlOptions['controller'] : 'index';
        $action = isset($urlOptions['action']) ? $urlOptions['action'] : 'index';
        $arUrl = parse_url(self::getUrlCurrent());
        return $arUrl['scheme'] . '://' . $arUrl['host'] . $arUrl['path'] .
            implode('/', [$module, $controller, $action]);
    }

    public static function getUrl(array $arrayRota)
    {
        $module = isset($arrayRota['module']) ? $arrayRota['module'] : 'application';
        $controller = isset($arrayRota['controller']) ? $arrayRota['controller'] : 'index';
        $action = isset($arrayRota['action']) ? $arrayRota['action'] : 'index';
        $serverUrl = new \Zend\View\Helper\ServerUrl();
        return $serverUrl->getScheme()
            . '://' . $serverUrl->getHost()
            . '/' . self::hifenizar($module)
            . '/' . self::hifenizar($controller)
            . '/' . self::hifenizar($action);
    }

    public static function getUrlCurrent()
    {
        $url = @($_SERVER["HTTPS"] != 'on') ?
            'http://' . $_SERVER["SERVER_NAME"] :
            'https://' . $_SERVER["SERVER_NAME"];
        $url .= $_SERVER["REQUEST_URI"];
        return $url;
    }

    public static function gzipAction()
    {
        // captura todo conteudo impresso na tela
        ob_start();
        /*
         * List of known content types based on file extension.
         * Note: These must be built-in somewhere...
         */
        $known_content_types = array(
            "htm" => "text/html",
            "html" => "text/html",
            "js" => "text/javascript",
            "css" => "text/css",
            "xml" => "text/xml",
            "gif" => "image/gif",
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png" => "image/png",
            "txt" => "text/plain"
        );

        /*
         * Get the path of the target file.
         */
        if (!isset($_GET['script'])) {
            header("HTTP/1.1 400 Bad Request");
            echo("<h1>HTTP 400 - Bad Request</h1>");
            exit;
        }
        $file = "." . $_GET['script'];

        /*
         * Verify the existence of the target file.
         * Return HTTP 404 if needed.
         */
        if (($src_uri = realpath($file)) === false) {
            /* The file does not exist */
            header("HTTP/1.1 404 Not Found");
            echo("<h1>HTTP 404 - Not Found</h1>");
            exit;
        }

        /*
         * Verify the requested file is under the doc root for security reasons.
         */
        $doc_root = realpath(".");
        if (strpos($src_uri, $doc_root) !== 0) {
            header("HTTP/1.1 403 Forbidden");
            echo("<h1>HTTP 403 - Forbidden</h1>");
            exit;
        }

        /*
         * Set the HTTP response headers that will
         * tell the client to cache the resource.
         */
        $file_last_modified = filemtime($src_uri);
        header("Last-Modified: " . date("r", $file_last_modified));
        $max_age = 300 * 24 * 60 * 60; // 300 days
        $expires = $file_last_modified + $max_age;
        header("Expires: " . date("r", $expires));
        $etag = dechex($file_last_modified);
        header("ETag: " . $etag);
        $cache_control = "must-revalidate, proxy-revalidate, max-age=" . $max_age . ", s-maxage=" . $max_age;
        header("Cache-Control: " . $cache_control);

        /*
         * Check if the client should use the cached version.
         * Return HTTP 304 if needed.
         */
        if (function_exists("http_match_etag") && function_exists("http_match_modified")) {
            if (http_match_etag($etag) || http_match_modified($file_last_modified)) {
                header("HTTP/1.1 304 Not Modified");
                exit;
            }
        } else {
            error_log("The HTTP extensions to PHP does not seem to be installed...");
        }

        /*
         * Extract the directory, file name and file
         * extension from the "uri" parameter.
         */
        $uri_dir = "";
        $file_name = "";
        $content_type = "";
        $uri_parts = explode("/", $src_uri);
        for ($i = 0; $i < count($uri_parts) - 1; $i++)
            $uri_dir .= $uri_parts[$i] . "/";

        $file_name = end($uri_parts);

        $file_parts = explode(".", $file_name);
        if (count($file_parts) > 1) {
            $file_extension = end($file_parts);
            $content_type = $known_content_types[$file_extension];
        }

        /*
         * Get the target file.
         * If the browser accepts gzip encoding, the target file
         * will be the gzipped version of the requested file.
         */
        $dst_uri = $src_uri;
        $compress = true;

        /*
         * Let's compress only text files...
         */
        $compress = $compress && (strpos($content_type, "text") !== false);

        /*
         * Finally, see if the client sent us the correct Accept-encoding: header value...
         */
        $compress = $compress && (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false);
        if ($compress) {
            // windows servers
            if (strpos($src_uri, ":") > 0) { // se tiver C: ou D:, etc
                $temp = str_replace("\\", "/", substr($src_uri, 3)); // remove o D: C: etc
                $uri_dir = substr($temp, 0, strrpos($temp, "/"));
            }
            $gz_uri = "tmp/gzip/" . $temp . ".gz";
            if (file_exists($gz_uri)) {
                $src_last_modified = filemtime($src_uri);
                $dst_last_modified = filemtime($gz_uri);
                // The gzip version of the file exists, but it is older
                // than the source file. We need to recreate it...
                if ($src_last_modified > $dst_last_modified)
                    unlink($gz_uri);
            }
            if (!file_exists($gz_uri)) {
                if (!file_exists("tmp/gzip/" . $uri_dir))
                    $this->_mkdir_r("tmp/gzip/" . $uri_dir);
                $error = false;
                if ($fp_out = gzopen($gz_uri, "wb")) {
                    if ($fp_in = fopen($src_uri, "rb")) {
                        while (!feof($fp_in))
                            gzwrite($fp_out, fread($fp_in, 1024 * 512));
                        fclose($fp_in);
                    } else {
                        $error = true;
                    }
                    gzclose($fp_out);
                } else {
                    $error = true;
                }
                if (!$error) {
                    $dst_uri = $gz_uri;
                    header("Content-Encoding: gzip");
                }
            } else {
                $dst_uri = $gz_uri;
                header("Content-Encoding: gzip");
            }
        }

        /*
         * Output the target file and set the appropriate HTTP headers.
         */
        if ($content_type)
            header("Content-Type: " . $content_type);
        header("Content-Length: " . filesize($dst_uri));
        readfile($dst_uri);
        ob_end_flush();
        exit(); // to not execute more anything after this
    }

    /*
     * The mkdir function does not support the recursive
     * parameter in the version of PHP run by Yahoo! Web
     * Hosting. This function simulates it.
     */

    public static function _mkdir_r($dir_name, $rights = 0777)
    {
        $dirs = explode("/", $dir_name);
        $dir = "";
        foreach ($dirs as $part) {
            $dir .= $part . "/";
            if (!is_dir($dir) && strlen($dir) > 0)
                mkdir($dir, $rights);
        }
    }

    /**
     * Lê os dados de um arquivo e os armazena em uma variável
     * @param string $filename Arquivo a ser lido
     * @return string Retorna o conteúdo do arquivo
     */
    public static function getIncludeContents($filename, $once = false)
    {
        if (self::fileExists($filename)) {
            ob_start();
            if ($once) {
                include_once $filename;
            } else {
                include $filename;
            }
            $conteudo = ob_get_clean();
            return $conteudo;
        }
        return NULL;
    }

    /**
     * Trata a data no formato pt-BR (25/01/2008) e retorna o formato desejado
     *
     * @name dataHoraIso
     * @param string $valor
     * @param string $formato
     * @return string
     */
    public static function dataBr($valor, $formato = 'YYYY-MM-dd')
    {
        if (strlen($valor) == 10) {
            list($dia, $mes, $ano) = explode("/", substr($valor, 0, 10));

            if (checkdate($mes, $dia, $ano))
                return $ano . "-" . $mes . "-" . $dia;
            else
                return "NULL";
        } else
            return "NULL";
    }

    /**
     * Retorna últimoa dia do mês
     * @param string $data
     * @return string
     */
    public static function ultimoDiaMes($mes, $ano)
    {
        return date("t", mktime(0, 0, 0, $mes, '01', $ano));
    }

    /**
     * Converte uma data no formato YYYY-MM-DD HH:MM
     * @param string $data
     * @return string
     */
    public static function converteDataDB2BR($data)
    {
        if (empty($data)) {
            return '';
        }

        $data = str_replace('/', '-', $data);
        $ar = sscanf($data, '%d-%d-%d %d:%d');
        $format_str = '%02d/%02d/%02d';
        if (count($ar[3])) {
            $format_str .= ' %02d:%02d';
        }
        return sprintf($format_str, $ar[2], $ar[1], $ar[0], $ar[3], $ar[4]);
    }
    
    public static function gerarSlug($str){
        $str = mb_strtolower($str); //Vai converter todas as letras maiúsculas pra minúsculas
        $str = preg_replace('/(â|á|ã)/', 'a', $str);
        $str = preg_replace('/(ê|é)/', 'e', $str);
        $str = preg_replace('/(í|Í)/', 'i', $str);
        $str = preg_replace('/(ú)/', 'u', $str);
        $str = preg_replace('/(ó|ô|õ|Ô)/', 'o',$str);
        $str = preg_replace('/(_|\/|!|\?|#)/', '',$str);
        $str = preg_replace('/( )/', '-',$str);
        $str = preg_replace('/ç/','c',$str);
        $str = preg_replace('/(-[-]{1,})/','-',$str);
        $str = preg_replace('/(,)/','-',$str);
        $str=strtolower($str);
        return $str;
    }

    /**
     * Converte uma data no formato YYYY-MM-DD HH:MM:SS para DD/MM/YYYY HH:MM:SS
     * @param string $data
     * @return string
     */
    public static function converteDataDB2BR_DMYHMS($data)
    {
        if (empty($data)) {
            return '';
        }
        $data = str_replace('/', '-', $data);
        $ar = sscanf($data, '%d-%d-%d %d:%d:%d');
        $format_str = '%02d/%02d/%02d';
        if (count($ar[3])) {
            $format_str .= ' %02d:%02d:%02d';
        }
        return sprintf($format_str, $ar[2], $ar[1], $ar[0], $ar[3], $ar[4], $ar[5]);
    }

    /**
     * Converte uma data no formato YYYY-MM-DD HH:MM
     * @param string $data
     * @return string
     */
    public static function converteDataBR2DB($data)
    {
        if (empty($data)) {
            return '';
        }

        $data = str_replace('/', '-', $data);
        $ar = sscanf($data, '%d-%d-%d %d:%d');
        $format_str = '%02d-%02d-%02d';
        if (count($ar[3])) {
            $format_str .= ' %02d:%02d';
        }

        return sprintf($format_str, $ar[2], $ar[1], $ar[0], $ar[3], $ar[4]);
    }

    /**
     * Converte uma data no formato DD/MM/YYYY HH:MM:SS para padrão americano de data YYYY/MM/DD
     * @param string $data
     * @return string
     */
    public static function converteDataYMD($data)
    {
        if (empty($data)) {
            return '';
        }

        $data = str_replace('/', '-', $data);
        $ar = sscanf($data, '%d-%d-%d %d:%d:%d');
        $format_str = '%02d-%02d-%02d';
        return sprintf($format_str, $ar[2], $ar[1], $ar[0], $ar[3], $ar[4]);
    }

    /**
     * Recebe uma data no formato ymd Hms e converte para dmy Hms
     * @param date $data
     * @return string
     */
    public static function formataDataDMY_HMS($data)
    {
        $ar = sscanf($data, '%d-%d-%d %d:%d');

        $novadata = sprintf('%d/%d/%d %d:%d', $ar[2], $ar[1], $ar[0], $ar[3]);
        return $novadata;
    }

    /**
     * Converte %u... para string
     * Retirada de www.php.net
     * @param string $str
     * @return string
     */
    public static function urlDecodeUTF8($str)
    {
        $str = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($str));
        return html_entity_decode($str, null, 'UTF-8');;
    }

    /**
     * Adicinona minutos a um timestamp
     * @param date $data data padrão, no formato dd/mm/YYYY h:i
     * @param integer $minutos número de minutos que será acrescentado
     * @return string com o novo timestamp
     */
    public static function addMinutos($timestamp, $minutos)
    {
        $timestamp = System::db()->converteDataBr2DB($timestamp);
        $data = strtotime($timestamp);
        return $data = date('d/m/Y H:i', mktime(date('H', $data), date('i', $data) + $minutos, 0, date('m', $data), date('d', $data), date('Y', $data)));
    }

    /**
     * Adicinona um número de dias a uma data
     * @param date $data data padrão, no formato YY/mm/dd
     * @param integer $dias número de dias que serão adicionados
     * @return string com a nova data
     */
    public static function addDias($data, $dias)
    {
        $data = self::dataBr($data);
        $data = strtotime($data);
        $date = date('Y-m-d', mktime(0, 0, 0, date('m', $data), date('d', $data) + $dias, date('Y', $data)));
        return self::converteDataDB2BR($date);
    }

    /**
     * Remove um número de dias a uma data
     * @param date $data data padrão, no formato d/m/Y
     * @param integer $dias número de dias que serão removidos
     * @return string com a nova data
     */
    public static function removeDias($data, $dias)
    {
        $ar = sscanf($data, '%d/%d/%d');
        return date('d/m/Y', mktime(0, 0, 0, $ar[1], $ar[0] - $dias, $ar[2]));
    }

    /**
     * Converte um decimal Brasileira para o de php
     * @param string $valor
     * @return string
     */
    public static function decimalBR2decimalPHP($valor)
    {
        $valor = str_replace('.', '', $valor);
        return floatval(str_replace(',', '.', $valor));
    }

    /**
     * Pega uma string DD/MMM/YY e retorna um timestamp no formato YYYY/MM/DD, que tb deve
     * ter entrado dessa forma
     * @param date $date uma data
     * @return timestamp $timestamp
     */
    public static function str2Timestamp($data)
    {
        $data = str_replace("-", '/', $data);
        $ar = sscanf($data, '%d/%d/%d');
        return mktime(0, 0, 0, $ar[1], $ar[0], $ar[2]);
    }

    /**
     * Retorna a diferença entre duas strings de datas, no formado DD/MM/YYYY
     * @param date $data_inicial data no formato string
     * @param date $data_final data no formato string
     * @return integer diferença de datas
     */
    public static function subtractDatas($data_inicial, $data_final)
    {
        // Usa a função criada e pega o timestamp das duas datas:
        $time_inicial = self::str2Timestamp($data_inicial);
        $time_final = self::str2Timestamp($data_final);

        // Calcula a diferença de segundos entre as duas datas:
        $diferenca = $time_final - $time_inicial;

        // Calcula a diferença de dias
        /**
         * @todo Usar floor ao invés de ceil?
         */
        $dias = (int)ceil($diferenca / (60 * 60 * 24));
        return $dias;
    }

    /**
     * Função que recebe uma data e converte para extenso
     * @param string $date data no formato dd/mm/yyyy
     * @return string Data por extenso
     */
    public static function data2Extenso($data)
    {
        $ar_data = sscanf($data, '%2d/%2d/%4d');
        return $ar_data[0] . ' de ' . Util::mesToString($ar_data[1]) . ' de ' . $ar_data[2];
    }

    /**
     * Converte de inteiro para string o Mês
     * @param int $mes
     * @return string
     */
    public static function mesToString($mes)
    {
        $ar_mes = array(1 => "Janeiro", 2 => "Fevereiro", 3 => "Março", 4 => "Abril",
            5 => "Maio", 6 => "Junho", 7 => "Julho", 8 => "Agosto",
            9 => "Setembro", 10 => "Outubro", 11 => "Novembro", 12 => "Dezembro");
        $mes = (settype($mes, "integer")) ? $ar_mes[$mes] : 'Desconhecido';
        return $mes;
    }

    public static function limparVetor(array $ar = array())
    {
        foreach ($ar as $chave => $valor) {
            if (empty($valor)) {
                unset($ar[$chave]);
            }
        }
        return $ar;
    }

    /**
     * Retira os acentos das letras
     * @param string $valor
     * @return string
     */
    public static function translate($valor, $upper = true)
    {
        $str = str_replace(explode(',', self::MINLATIN), explode(',', self::TRANSLATIN), self::strToLower($valor));
        $str = str_replace(explode(',', self::MAXLATIN), explode(',', self::TRANSLATIN), self::strToLower($str));
//        if ($upper) {
//            return self::strToUpper($str);
//        }
//        return $str;
    }

    /**
     * Retira os acentos das letras
     * @param string $valor
     * @return string
     */
    public static function trans($valor, $upper = true)
    {
        $str = str_replace(explode(',', self::MINLATIN), explode(',', self::TRANSLATIN), $valor);
        $str = str_replace(explode(',', self::MAXLATIN), explode(',', self::TRANSLATIN), $str);
//        if ($upper) {
//            return self::strToUpper($str);
//        }
        return $str;
    }

    /**
     * Transforma a string to uppercase levanto em conta caracteres latinos
     * @param string $valor
     * @return string
     */
    public static function strToUpper($valor)
    {
        return mb_strtoupper($valor, 'UTF-8');
        return str_replace(explode(',', self::MINLATIN), explode(',', self::MAXLATIN), strtoupper($valor));
    }

    /**
     * Transforma a string para lowercase levando em conta caracteres latinos
     * @param <type> $valor
     * @return <type>
     */
    public static function strToLower($valor)
    {
        setlocale(LC_ALL, "portuguese", "pt_BR", "pt_BR.iso88591", "pt_BR.utf8");
        setlocale(LC_CTYPE, "pt_BR");
//        return strtolower($valor);
        return str_replace(explode(',', self::MAXLATIN), explode(',', self::MINLATIN), strtolower($valor));
    }

    /**
     * Retorna uma frase com a primeira letra de cada palavra em maiúsculo e
     * as restantes minúsculo.
     * @param string $string frase a ser analisada
     * @return string frase com primeiras letras maiúsculas
     */
    public static function ucwords($string)
    {
        return $string;
        $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
        return $string;
    }

    public static function bool($string_bool)
    {
        $bo = false;
        if ($string_bool == 't' || $string_bool == 'true' || $string_bool == '1' || $string_bool == 'on')
            $bo = true;
        return $bo;
    }

    public static function bool2String($bool)
    {
        $string = 'false';
        if ($bool)
            $string = 'true';
        return $string;
    }

//    public static function serializar( $obj ) {
//       return base64_encode(gzcompress(serialize($obj)));
//    }
//
//    public static function desserializar( $txt ) {
//       return unserialize(gzuncompress(base64_decode($txt)));
//    }

    public static function in_array_r($needle, $haystack)
    {
        foreach ($haystack as $item) {
            if ($item == $needle || (is_array($item) && self::in_array_r($needle, $item))) {
                return true;
            }
        }
        return false;
    }

    public static function in_array($needle, $haystack)
    {
        foreach ($haystack as $chave => $valor) {
            if ($valor == $needle) {
                return true;
            }
        }
        return false;
    }

    public static function hifenizar($str)
    {
        if (empty($str)) {
            return;
        }
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "-" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    public static function camelCase($palavra)
    {
        $palavra = str_replace('_', '-', $palavra);
        $ar = explode('-', $palavra);
        foreach ($ar as $chave => $valor) {
//            if($chave != 0) {
            $ar[$chave] = ucwords($valor);
//            }
        }
        return implode('', $ar);
    }
    
    public static function tratar_nome ($nome) {
        $nome = strtolower($nome); // Converter o nome todo para minúsculo
        $nome = explode(" ", $nome); // Separa o nome por espaços
        $saida = "";
        for ($i=0; $i < count($nome); $i++) {

            // Tratar cada palavra do nome
            if ($nome[$i] == "de" or $nome[$i] == "da" or $nome[$i] == "e" or $nome[$i] == "dos" or $nome[$i] == "do") {
                $saida .= $nome[$i].' '; // Se a palavra estiver dentro das complementares mostrar toda em minúsculo
            }else {
                $saida .= ucfirst($nome[$i]).' '; // Se for um nome, mostrar a primeira letra maiúscula
            }

        }
        return $saida;
    }

    public static function camelCaseInvert($input, $separador = '-')
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode($separador, $ret);
    }

    /**
     * @deprecated Função com erros quando se trata dos últimos dias do ano.
     * Utilizar a função formatDate dessa mesma classe.
     */
    public static function dataIso($valor, $formato = 'YYYY-MM-dd')
    {
        if (strlen($valor) == 10) {
            list($ano, $mes, $dia) = explode("-", substr($valor, 0, 10));
            return $dia . "/" . $mes . "/" . $ano;
        } else {
            return "NULL";
        }
    }

    public static function moeda($valor, $simbolo = false, $precision = 2)
    {
        $zCurrrency = new Zend_Currency(array('precision' => $precision));

        if (!$simbolo)
            $zCurrrency->setFormat(array('display' => Zend_Currency::NO_SYMBOL));
        else
            $zCurrrency->setFormat(array('display' => Zend_Currency::USE_SYMBOL));

        return $zCurrrency->toCurrency((double)$valor);
    }

    /**
     * Insere máscara (pontuação) em números de CPF/CNPJ
     *
     * @name cpfCnpj
     * @param string $numero
     * @return string
     */
    public static function cpfCnpj($numero)
    {
        $numero = trim(self::removerPontuacao($numero));
        switch (strlen($numero)) {
            case 11:
                return substr($numero, 0, 3) . '.' . substr($numero, 3, 3) . '.' . substr($numero, 6, 3) . '-'
                    . substr($numero, 9, 2);
                break;
            case 14:
                return substr($numero, 0, 2) . '.' . substr($numero, 2, 3) . '.' . substr($numero, 5, 3) . '/'
                    . substr($numero, 8, 4) . '-' . substr($numero, 12, 2);
                break;
        }
    }

    /**
     * Remove a pontuação de um conteúdo
     *
     * @name removerPontuacao
     * @param string $conteudo
     * @return string
     */
    public static function removerPontuacao($conteudo)
    {
        return str_replace(array('-', ',', '.', '/', '\\', ';', ':', '?', '!'), '', $conteudo);
    }

    /**
     * Remove a pontuação de um conteúdo
     *
     * @name removerPontuacao
     * @param string $conteudo
     * @return string
     */
    public static function removerCaracteresEspeciaisEspacos($conteudo)
    {
        return str_replace(array('(', ')', '[', ']', '{', '}', '-', ',', '.', '/', '\\', ';', ':', '?', '!', ' '), '', $conteudo);
    }

    /**
     * Remove a pontuação de um conteúdo
     *
     * @name removerPontuacao
     * @param string $conteudo
     * @return string
     */
    public static function removerCaracteresEspeciais($conteudo)
    {
        return str_replace(array('(', ')', '[', ']', '{', '}', '-', ',', '.', '/', '\\', ';', ':', '?', '!', '&', '\'', '"'), '', $conteudo);
    }

    public static function tratarCaracteresNaoReconhecidos($conteudo)
    {
        return str_replace('�', '', $conteudo);
    }

    /**
     * Formata o valor para o mesmo tenha n casas decimais
     *
     * @name numeroDecimal
     * @param float $valor
     * @return float
     */
    public static function numeroDecimal($valor, $casasDecimais = 2)
    {
        return number_format($valor, $casasDecimais, '.', '');
    }

    /**
     * Retorna a diferença de dias entre duas datas
     * Formato das datas: YYYY-MM-DD
     *
     * @name diasEntreDatas
     * @param string $valor
     * @return string
     */

    public static function diasEntreDatas($dataInicio, $dataFim)
    {
        $dataInicio = self::formatDate($dataInicio);
        $dataFim = self::formatDate($dataFim);

        $dataInicio = self::inverterData($dataInicio);
        $dataFim = self::inverterData($dataFim);

        $startDate = new \DateTime($dataInicio);
        $endDate = new \DateTime($dataFim);
        $interval = $startDate->diff($endDate);
        $days = $interval->format("%r%a");
        return $days;
    }

    public static function inverterData($data)
    {
        if (count(explode("/", $data)) > 1) {
            return implode("-", array_reverse(explode("/", $data)));
        }
    }

    /**
     * Transforma caracteres especiais em código html
     * @param string $palavra Palavara
     * @return string
     */
    public static function acento2Html($palavra)
    {
        $comacento = array('Á', 'á', 'Â', 'â', 'À', 'à', 'Ã', 'ã', 'É', 'é', 'Ê', 'ê', 'È', 'è', 'Ó', 'ó', 'Ô', 'ô', 'Ò', 'ò', 'Õ', 'õ', 'Í', 'í', 'Î', 'î', 'Ì', 'ì', 'Ú', 'ú', 'Û', 'û', 'Ù', 'ù', 'Ç', 'ç',);
        $acentohtml = array('&Aacute;', '&aacute;', '&Acirc;', '&acirc;', '&Agrave;', '&agrave;', '&Atilde;', '&atilde;', '&Eacute;', '&eacute;', '&Ecirc;', '&ecirc;', '&Egrave;', '&egrave;', '&Oacute;', '&oacute;', '&Ocirc;', '&ocirc;', '&Ograve;', '&ograve;', '&Otilde;', '&otilde;', '&Iacute;', '&iacute;', '&Icirc;', '&icirc;', '&Igrave;', '&igrave;', '&Uacute;', '&uacute;', '&Ucirc;', '&ucirc;', '&Ugrave;', '&ugrave;', '&Ccedil;', '&ccedil;');
        $palavra = str_replace($comacento, $acentohtml, $palavra);
        return $palavra;
    }

    public static function html2Acento($palavra)
    {
        $comacento = array('Á', 'á', 'Â', 'â', 'À', 'à', 'Ã', 'ã', 'É', 'é', 'Ê', 'ê', 'È', 'è', 'Ó', 'ó', 'Ô', 'ô', 'Ò', 'ò', 'Õ', 'õ', 'Í', 'í', 'Î', 'î', 'Ì', 'ì', 'Ú', 'ú', 'Û', 'û', 'Ù', 'ù', 'Ç', 'ç', ' ');
        $acentohtml = array('&Aacute;', '&aacute;', '&Acirc;', '&acirc;', '&Agrave;', '&agrave;', '&Atilde;', '&atilde;', '&Eacute;', '&eacute;', '&Ecirc;', '&ecirc;', '&Egrave;', '&egrave;', '&Oacute;', '&oacute;', '&Ocirc;', '&ocirc;', '&Ograve;', '&ograve;', '&Otilde;', '&otilde;', '&Iacute;', '&iacute;', '&Icirc;', '&icirc;', '&Igrave;', '&igrave;', '&Uacute;', '&uacute;', '&Ucirc;', '&ucirc;', '&Ugrave;', '&ugrave;', '&Ccedil;', '&ccedil;', '&nbsp;');
        $palavra = str_replace($acentohtml, $comacento, $palavra);
        return $palavra;
    }

    public static function mascararPropriedade($cod_propriedade)
    {
        return Util::mask($cod_propriedade, '##.#####.####');
    }

    public static function mascararUP($cod_up)
    {
        return Util::mask($cod_up, '##.#####.####.##.###');
    }

    public static function mascararIe($ie)
    {
        return Util::mask($ie, '##.###.###-#');
    }

    public static function mascararCpfCnpj($cpf_cnpj)
    {
        $cpf_cnpj = util::removerCaracteresEspeciaisEspacos($cpf_cnpj);
        if (strlen($cpf_cnpj)) {
            if (strlen($cpf_cnpj) == 11) {
                $cpf_cnpj = Util::mascararCpf($cpf_cnpj);
            } else if (strlen($cpf_cnpj) == 14) {
                $cpf_cnpj = Util::mascararCnpj($cpf_cnpj);
            }
            return $cpf_cnpj;
        }
    }

    public static function mascararCpf($cpf)
    {
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('/', '', $cpf);
        $cpf = str_replace('-', '', $cpf);
        return Util::mask($cpf, '###.###.###-##');
    }

    public static function mascararCnpj($cnpj)
    {
        $cnpj = str_replace('.', '', $cnpj);
        $cnpj = str_replace('/', '', $cnpj);
        $cnpj = str_replace('-', '', $cnpj);
        return Util::mask($cnpj, '##.###.###/####-##');
    }

    public static function mascararLatitudeLongitude($coordenada)
    {
        return Util::mask($coordenada, '##°##\'##.#\'\'');
    }

    private static function mask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        $val = str_split($val);
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    /**
     * Transforma um array em cabecalho pra ser enviado a um post
     * @param array $ar_post Array contendo valores na chave
     * @return string Uma string post no formato pametro1=valor1&parametro2=valor2
     */
    public static function arrayToPost($ar_post)
    {
        $ar_string = array();
        if (!is_array($ar_post) || empty($ar_post)) {
            return $ar_string;
        }
        foreach ($ar_post as $chave => $valor) {
            $ar_string[] = $chave . "=" . str_replace("'", "\'", $valor);
        }
        return implode('&', $ar_string);
    }

    public static function ArrayToTagTable($ar_valores, $label, $descricao)
    {
        if (count($ar_valores)) {
            $ar[] = "<table id='tableDetalhe'>";
            foreach ($ar_valores as $chave => $valor) {
                $lbl = (isset($valor[$label])) ? $valor[$label] : "";
                $vl = (isset($valor[$descricao])) ? $valor[$descricao] : "";
                $ar[] = "<tr><td>{$lbl}</td><td>{$vl}</td></tr>";
            }
            $ar[] = "</table>";
            return implode("", $ar);
        }
    }

    public static function formatarMoeda($numero)
    {
        return number_format($numero, 2, ',', '.');
    }

    public static function formatarCpfCnpj($cpf_cnpj)
    {
        $cpf_cnpj = self::removerCaracteresEspeciaisEspacos($cpf_cnpj);
        if (strlen($cpf_cnpj)) {
            if (strlen($cpf_cnpj) == 11) {
                $cpf_cnpj = Util::mascararCpf($cpf_cnpj);
            } else if (strlen($cpf_cnpj) == 14) {
                $cpf_cnpj = Util::mascararCnpj($cpf_cnpj);
            }
            return $cpf_cnpj;
        }
    }

    /**
     * Formatar telefone para formato brasileiro
     *
     * @param string $telefone Sequência de números de telefone
     * @return string Telefone formatado
     */
    public static function formatarTelefone($telefone)
    {
        // Buscar apenas os numeros
        $len = strlen($telefone);
        $total_numeros = 0;
        $numeros = '';
        for ($i = 0; $i < $len; $i++) {
            $c = $telefone[$i];
            if (ctype_digit($c)) {
                $numeros .= $c;
                $total_numeros += 1;
            }
        }
        // Analisar de acordo com quantidade de numeros informados
        switch ($total_numeros) {

            // Se informou pais, zero, operadora, DDD e numero
            // 55 0PPDD XXXX XXXX
            case 15:
                if (preg_match('/^(55)0[\d]{2}([\d]{2})([\d]{4})([\d]{4})$/', $numeros, $match)) {
                    return sprintf('+%02d (%02d) %04d-%04d', $match[1], $match[2], $match[3], $match[4]);
                }
                return false;

            // Se informou pais, operadora, DDD e numero
            // 55 PPDD XXXX XXXX
            case 14:
                if (preg_match('/^(55)[\d]{2}([\d]{2})([\d]{4})([\d]{4})$/', $numeros, $match)) {
                    return sprintf('+%02d (%02d) %04d-%04d', $match[1], $match[2], $match[3], $match[4]);
                }
                return false;

            // Se informou pais, zero, DDD e numero
            // 55 0DD XXXX XXXX
            // Se informou zero, operadora, DDD e numero
            // 0PPDD XXXX XXXX
            case 13:
                if (preg_match('/^(55)0([\d]{2})([\d]{4})([\d]{4})$/', $numeros, $match)) {
                    return sprintf('+%02d (%02d) %04d-%04d', $match[1], $match[2], $match[3], $match[4]);
                } elseif (preg_match('/^0[\d]{2}([\d]{2})([\d]{4})([\d]{4})$/', $numeros, $match)) {
                    return sprintf('(%02d) %04d-%04d', $match[1], $match[2], $match[3]);
                }
                return false;

            // Se informou pais, DDD e numero
            // 55 DD XXXX XXXX
            case 12:
                if (preg_match('/^(55)([\d]{2})([\d]{4})([\d]{4})$/', $numeros, $match)) {
                    return sprintf('+%02d (%02d) %04d-%04d', $match[1], $match[2], $match[3], $match[4]);
                }
                return false;

            // Se informou zero, DDD e numero
            // 0DD XXXX XXXX
            case 11:
                if (preg_match('/^0([\d]{2})([\d]{4})([\d]{4})$/', $numeros, $match)) {
                    return sprintf('(%02d) %04d-%04d', $match[1], $match[2], $match[3]);
                }
                return false;

            // Se informou DDD e numero
            // DD XXXX XXXX
            case 10:
                if (preg_match('/^([\d]{2})([\d]{4})([\d]{4})$/', $numeros, $match)) {
                    return sprintf('(%02d) %04d-%04d', $match[1], $match[2], $match[3]);
                }
                return false;

            // Se informou numero
            // XXXX XXXX
            case 8:
                if (preg_match('/^([\d]{4})([\d]{4})$/', $numeros, $match)) {
                    return sprintf('(%02d) %04d-%04d', $codigo_padrao, $match[1], $match[2]);
                }
                return false;
        }

        return false;
    }

    /** O valor de $moeda, necessariamente precisa estar no formato R$ XXX.XXX,XX para funcionar **/
    public static function removerFormatacaoValor($moeda)
    {
        //$valor = self::removerFormatacao($moeda, array(' ', 'R$', '.'));
        //return str_replace(",", ".", $valor);
        $charac = array(' ', 'R$', '.');
        foreach ($charac AS $char) {
            $moeda = str_replace($char, '', $moeda);
        }
        return str_replace(",", ".", $moeda);
    }

    public static function formataMoedaSQL($valor)
    {
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(",", ".", $valor);
        return $valor;

    }

    public static function removerFormatacao($valor, array $charac = array())
    {
        $texto = preg_replace('/[^A-Za-z0-9\s.\s-]/', '', $valor);
        $sem_espaco = str_replace(" ", "", $texto);
        return str_replace("-", "", $sem_espaco);
    }

    public static function removerFormatacaoCPFCNPJ($valor)
    {
        return str_replace(['.', '-', '/'], "", $valor);
    }

    public static function hexToStr($hex)
    {
        $string = '';
        $length = (strlen($hex));
        $length -= 1;
        for ($i = 0; $i < $length; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }

    public static function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    /**
     * find the numeric index of a key in an array
     * @param type $arr
     * @param type $key
     * @return int
     */
    public static function array_key_index(&$arr, $key)
    {
        $i = 0;
        foreach (array_keys($arr) as $k) {
            if ($k == $key)
                return $i;
            $i++;
        }
    }

    public static function mascara_telefone($telefone)
    {
        $telefone = self::removerCaracteresEspeciaisEspacos($telefone);
        if (strlen($telefone) == 11) {
            return self::mascara_string('(##)#####-####', $telefone);
        } else if (strlen($telefone) == 10) {
            return self::mascara_string('(##)####-####', $telefone);
        } else {
            return self::mascara_string('####-####', $telefone);
        }
    }

    public static function mascara_data($data)
    {
        return self::mascara_string('##/##/####', $data);
    }

    public static function mascara_horario($horario)
    {
        return self::mascara_string('##:##', $horario);
    }

    public static function mascara_string($mascara, $string)
    {
        $string = str_replace(" ", "", $string);
        for ($i = 0; $i < strlen($string); $i++) {
            $mascara[strpos($mascara, "#")] = $string[$i];
        }
        return $mascara;
    }

    /** @todo função quebra-galho até conseguir dominar translate */
    public static function desacentuar($texto)
    {
        $array1 = array("á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç"
        , "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç");
        $array2 = array("a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c"
        , "A", "A", "A", "A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C");
        return str_replace($array1, $array2, $texto);
    }

    public static function utf8_urldecode($str)
    {
        $str = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($str));
        return html_entity_decode($str, null, 'UTF-8');;
    }

    public static function m2ToHa($m2)
    {
        $m2 = $m2 / 1000;
        return $m2;
    }

    public static function criptografar_gta($documento)
    {
        $array1 = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $array2 = array("$", "t", "*", ")", "@", "!", "p", "(", "^", ";");
        return str_replace($array1, $array2, $documento);
    }

    public static function getHora($data)
    {
        $ar = explode(' ', $data);
        $ar_hora = explode(':', $ar[1]);
        if (isset($ar_hora[0])) {
            return $ar_hora[0];
        }
    }

    public static function getMinuto($data)
    {
        $ar = explode(' ', $data);
        $ar_hora = explode(':', $ar[1]);
        if (isset($ar_hora[1])) {
            return $ar_hora[1];
        }
    }

    public static function getSenha123abc()
    {
        $senha = md5('123abc');
        return $senha;
    }

    public static function milhar($number)
    {
        return number_format(round($number, 0), 0, '', '.');
    }

    /**
     * Identificar a extensão do arquivo
     */
    public static function findexts($filename)
    {
        $filename = strtolower($filename);
        $exts = split("[/\\.]", $filename);
        $n = count($exts) - 1;
        $exts = $exts[$n];

        return $exts;
    }

    public static function removerFormatacaoValorWithDot($moeda)
    {
        $valor = self::removerFormatacao($moeda, array(' ', 'R$'));
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(",", ".", $valor);
        return $valor;
    }

    public static function removerUnidadeValor($moeda)
    {
        $valor = self::removerFormatacao($moeda, array(' ', 'R$'));
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(",", ".", $valor);
        return $valor;
    }

    //Compara dois números com ponto flutuante
    public static function floatcmp($f1, $f2, $precision = 10)
    {
        $e = pow(10, $precision);
        return (intval($f1 * $e) < intval($f2 * $e));
    }

    public static function fileExists($file)
    {
        return file_exists($file) && !is_dir($file);
    }

    public static function HaTom2($ha)
    {
        $m2 = $ha * 1000;
        return $m2;
    }

    // Função para calcular os dias úteis entre datas, excluindo sábados e domingos
    public static function calcula_dias_uteis($dataInicial, $dataFinal)
    {
        $data_inicial = explode("/", $dataInicial);
        $data_final = explode("/", $dataFinal);

        $diaInicial = $data_inicial[0];
        $mesInicial = $data_inicial[1];
        $anoInicial = $data_inicial[2];

        $diaFinal = $data_final[0];
        $mesFinal = $data_final[1];
        $anoFinal = $data_final[2];

//calculo timestam das duas datas
        $timestamp1 = mktime(0, 0, 0, $mesFinal, $diaFinal, $anoFinal);
        $timestamp2 = mktime(4, 12, 0, $mesInicial, $diaInicial, $anoInicial);

//diminuo a uma data a outra
        $segundos_diferenca = $timestamp1 - $timestamp2;

        $dias_diferenca = (int)ceil($segundos_diferenca / (60 * 60 * 24));

        $totalFinalSemana = 0;
        for ($x = 0; $x < $dias_diferenca; $x++) {
            $diaSemana = date("w", strtotime("+" . $x . " day", strtotime($anoInicial . "-" . $mesInicial . "-" . $diaInicial)));
            $dia = date("d", strtotime("+" . $x . " day", strtotime($anoInicial . "-" . $mesInicial . "-" . $diaInicial)));
            $mes = date("m", strtotime("+" . $x . " day", strtotime($anoInicial . "-" . $mesInicial . "-" . $diaInicial)));
            $ano = date("Y", strtotime("+" . $x . " day", strtotime($anoInicial . "-" . $mesInicial . "-" . $diaInicial)));

//verifica se o dia da semana é sábado ou domingo
            if ($diaSemana == 6 || $diaSemana == 0) {
                $totalFinalSemana++;
            }

        }
        $diferenca = $dias_diferenca - $totalFinalSemana;
        return $diferenca;
    }

    public static function tokenGta($string)
    {
        $alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
        $ar = str_split(strrev($string));
        $new_ar = array();
        foreach ($ar as $value) {
            $new_ar[] = $alphabet[$value];
        }
        return 'ktone' . implode('', $new_ar) . 'tga';
    }

    public static function generateRandomString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function formatDate($data)
    {
        if (empty($data)) {
            return "-";
        }
        if (strpos($data, '/') === false) {
            return date("d/m/Y", strtotime($data));
        } else {
            return date("d/m/Y", strtotime(str_replace('/', '-', $data)));
        }
    }

    public static function formatDateHour($data)
    {
        if (empty($data)) {
            return "-";
        }
        if (strpos($data, '/') === false) {
            return date("d/m/Y H:i", strtotime($data));
        } else {
            return date("d/m/Y H:i", strtotime(str_replace('/', '-', $data)));
        }
    }

    public static function formatDateHourSeconds($data)
    {
        if (empty($data)) {
            return "-";
        }
        if (strpos($data, '/') === false) {
            return date("d/m/Y H:i:s", strtotime($data));
        } else {
            return date("d/m/Y H:i:s", strtotime(str_replace('/', '-', $data)));
        }
    }

    public static function traduzirBooleano($ativo)
    {
        $valor = '<label class="label label-important" >Não</label>';
        if ($ativo == 't' || $ativo == 'true' || $ativo == '1') {
            $valor = '<label class="label label-success" >Sim</label>';
        }
        return $valor;
    }

    public static function is_utf8($str)
    {
        $c = 0;
        $b = 0;
        $bits = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c >= 254)) return false;
                elseif ($c >= 252) $bits = 6;
                elseif ($c >= 248) $bits = 5;
                elseif ($c >= 240) $bits = 4;
                elseif ($c >= 224) $bits = 3;
                elseif ($c >= 192) $bits = 2;
                else return false;
                if (($i + $bits) > $len) return false;
                while ($bits > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) return false;
                    $bits--;
                }
            }
        }
        return true;
    }

    /**
     * Checa se o script que rodou php foi via apache ou CGI. Em caso positivo
     * indica que foi um script cgi, rodado no shell.
     *
     * @return boolean Indica se o acesso foi via shell.
     */
    public static function isCli()
    {
        $bo = false;
        $sapi_type = php_sapi_name();
        if (substr($sapi_type, 0, 3) == 'cli') {
            $bo = true;
        }
        return $bo;

    }

    /**
     * Identifica se o acesso ao sistema foi via desktop ou mobile. Retorna
     * positivo para mobile.
     *
     * @return bool Verdadeiro para acesso mobile.
     */
    public static function isMobile()
    {
        $detector = new \Agrodefesa\Validation\Mobile();
        return $detector->isMobile();
    }

    /**
     * Esta função recebe dois números como parâmetro.
     * Se os números forem iguais, ou seja, se a diferença entre eles for menor que a margem de erro aceitável,
     * a função retorna 0, caso contrário retorna -1 se o primeiro número for menor,
     * ou então 1 caso o segundo seja o menor
     * @param float $a
     * @param float $b
     * @return 0 (igual), -1($num1 menor), 1($num2 menor)
     */
    public static function compara_float($num1, $num2, $precisao = 5)
    {
        $desprezar = pow(1, -1 * $precisao);
        $diff = abs($num1 - $num2);
        if ($diff < $desprezar) {
            return 0;
        }
        return $num1 < $num2 ? -1 : 1;
    }

    /**
     * Se um valor estiver formatado para moeda, aqui há uma desformatação. O
     * valor retornado é um float com o valor correspondente.
     * @param string $money_texto Valor em moeda formatado
     * @return float Valor float correspondente
     */
    public static function moneyToFloat($money_texto)
    {
        $semString = preg_replace('/([^0-9\.,])/i', '', $money_texto);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money_texto);

        $separatorsCountToBeErased = strlen($semString) - strlen($onlyNumbersString) - 1;

        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $semString, $separatorsCountToBeErased);
        $removedThousendSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '', $stringWithCommaOrDot);

        return (float)str_replace(',', '.', $removedThousendSeparator);
    }

    public static function tratarValorNumerico($valor)
    {
        if (strpos($valor, ',') !== false) {
            $valor = str_replace(".", "", $valor);
            $valor = str_replace(",", ".", $valor);
        }
        return $valor;
    }

    public static function formatarArea($numero, $casasDecimais = 4)
    {
        return number_format($numero, $casasDecimais, ',', '.');
    }

    /**
     * Recebe um array e verifica se tem valores duplicados retornando true ou false.
     * @param array $array Array a ser verificado
     * @return boolean Booleano true ou false
     */
    public static function arrayTemValoresDuplicados($array)
    {
        $dupe_array = array();
        foreach ($array as $val) {
            if (!isset($dupe_array[$val])) {
                $dupe_array[$val] = 1;
            } else {
                ++$dupe_array[$val];
            }
            if ($dupe_array[$val] > 1) {
                return true;
            }
        }
        return false;
    }

    public static function traduzirSituacaoAtivoInativo($ativo)
    {
        $valor = '<label class="label label-important" >Inativo</label>';
        if ($ativo == 't' || $ativo == 'true' || $ativo == '1') {
            $valor = '<label class="label label-success" >Ativo</label>';
        }
        return $valor;
    }

    public static function array_column($array, $column)
    {
        $ret = array();
        foreach ($array as $row) {
            $ret[] = $row[$column];
        }
        return $ret;
    }


    public static function arrayToXml($array, $level = 1)
    {
        $xml = '';
        // if ($level==1) {
        //     $xml .= "<array>\n";
        // }
        foreach ($array as $key => $value) {
//            $key = strtolower($key);
            if (is_object($value)) {
                $value = get_object_vars($value);
            }// convert object to array

            if (is_array($value)) {
                $multi_tags = false;
                foreach ($value as $key2 => $value2) {
                    if (is_object($value2)) {
                        $value2 = get_object_vars($value2);
                    } // convert object to array
                    if (is_array($value2)) {
                        $xml .= str_repeat("\t", $level) . "<$key>\n";
                        $xml .= array_to_xml($value2, $level + 1);
                        $xml .= str_repeat("\t", $level) . "</$key>\n";
                        $multi_tags = true;
                    } else {
                        if (trim($value2) != '') {
                            if (htmlspecialchars($value2) != $value2) {
                                $xml .= str_repeat("\t", $level) .
                                    "<$key2><![CDATA[$value2]]>" . // changed $key to $key2... didn't work otherwise.
                                    "</$key2>\n";
                            } else {
                                $xml .= str_repeat("\t", $level) .
                                    "<$key2>$value2</$key2>\n"; // changed $key to $key2
                            }
                        }
                        $multi_tags = true;
                    }
                }
                if (!$multi_tags and count($value) > 0) {
                    $xml .= str_repeat("\t", $level) . "<$key>\n";
                    $xml .= array_to_xml($value, $level + 1);
                    $xml .= str_repeat("\t", $level) . "</$key>\n";
                }

            } else {
                if (trim($value) != '') {
                    echo "value=$value<br>";
                    if (htmlspecialchars($value) != $value) {
                        $xml .= str_repeat("\t", $level) . "<$key>" .
                            "<![CDATA[$value]]></$key>\n";
                    } else {
                        $xml .= str_repeat("\t", $level) .
                            "<$key>$value</$key>\n";
                    }
                }
            }
        }
        //if ($level==1) {
        //    $xml .= "</array>\n";
        // }
        return $xml;
    }

    public static function arrayColuna($vetor, $coluna)
    {
        $arr = [];

        foreach ($vetor as $valor) {
            if (isset($valor[$coluna])) {
                $arr[] = $valor[$coluna];
            }
        }
        return $arr;
    }

    public static function validarEmail($email)
    {
        if (!preg_match('/\S+@\S+\.\S+/', $email)) {
            return false;
        } else {
            return true;
        }
    }

    public static function class_namespaced()
    {
        return __CLASS__;
    }

    public static function processarFK($valor_fk)
    {
        $ar = array();
        $ar_pedido = explode(DELIMITADOR, str_replace(DELIMITADOR_CAMPO, '&', $valor_fk));

        foreach ($ar_pedido as $valor) {

            if (!empty($valor)) {
                $ar_str = array();

                parse_str($valor, $ar_str);
                foreach ($ar_str as $key => $value) {
                    if (empty($value)) {
                        $ar_str[$key] = null;
                    }
                }
                reset($ar_str);
                if (key($ar_str) != 'oid') { // campo enviado e utilizado somente na view html
                    if (!empty($ar_str)) {
                        $ar[] = $ar_str;
                    }
                }
            }
        }

        return $ar;
    }

    public static function timeAgo($time)
    {
        $diff = time() - $time;
        $seconds = $diff;
        $minutes = round($diff / 60);
        $hours = round($diff / 3600);
        $days = round($diff / 86400);
        $weeks = round($diff / 604800);
        $months = round($diff / 2419200);
        $years = round($diff / 29030400);

        if ($seconds <= 60) $retorno = "$seconds segundos atrás";
        else if ($minutes <= 60) $retorno = $minutes == 1 ? '1 minuto atrás' : $minutes . ' minutos atrás';
        else if ($hours <= 24) $retorno = $hours == 1 ? '1 hora atrás' : $hours . ' horas atrás';
        else if ($days <= 7) $retorno = $days == 1 ? '1 dia atrás' : $days . ' dias atrás';
        else if ($weeks <= 4) $retorno = $weeks == 1 ? '1 semana atrás' : $weeks . ' semanas atrás';
        else if ($months <= 12) $retorno = $months == 1 ? '1 mês atrás' : $months . ' meses atrás';
        else $retorno = $years == 1 ? '1 ano atrás' : $years . ' anos atrás';
        return $retorno;
    }

    public static function findWhere($array, $matching)
    {
        foreach ($array as $item) {
            $isMatch = true;
            foreach ($matching as $key => $value) {
                if (is_object($item)) {
                    if (!isset($item->$key)) {
                        $isMatch = false;
                        break;
                    }
                } else {
                    if (!isset($item[$key])) {
                        $isMatch = false;
                        break;
                    }
                }
                if (is_object($item)) {
                    if ($item->$key != $value) {
                        $isMatch = false;
                        break;
                    }
                } else {
                    if ($item[$key] != $value) {
                        $isMatch = false;
                        break;
                    }
                }
            }
            if ($isMatch) {
                return $item;
            }
        }
        return false;
    }

    public static function orderWhere()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    public static function porcentagem($parcial, $total)
    {
        $resultado = 0;
        if ($parcial > 0) {
            $resultado = ($total * 100) / $parcial;
            if ($resultado > 100) {
                $resultado = 100;
            }
        }
        return number_format($resultado, 2);
    }


    /**
     * Transforma um valor do banco do tipo ARRAY para um array a ser usado no
     * componente jQuery CHOSEN
     * @param String $valor
     * @return Array $valor
     */
    public static function pgArrayToChosen($valor)
    {
        $valor = str_replace("{", "", $valor);
        $valor = str_replace("}", "", $valor);
        return explode(",", $valor);
    }


    public static function validarCpf($cpf = null) {

        // Verifica se um número foi informado
        if(empty($cpf)) {
            return false;
        }

        // Elimina possivel mascara
        $cpf = preg_replace('[^0-9]', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Verifica se o numero de digitos informados é igual a 11
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo
        // foi digitada. Caso afirmativo, retorna falso
        else if ($cpf == '00000000000' ||
            $cpf == '11111111111' ||
            $cpf == '22222222222' ||
            $cpf == '33333333333' ||
            $cpf == '44444444444' ||
            $cpf == '55555555555' ||
            $cpf == '66666666666' ||
            $cpf == '77777777777' ||
            $cpf == '88888888888' ||
            $cpf == '99999999999') {
            return false;
            // Calcula os digitos verificadores para verificar se o
            // CPF é válido
        } else {

            for ($t = 9; $t < 11; $t++) {

                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }

            return true;
        }
    }

    public static function validarCnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string)$cnpj);
        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
    }

    /*public static function validarCnpj($cnpj)
    {
        $cnpj = Util::removerCaracteresEspeciaisEspacos($cnpj);
        $cnpj = trim($cnpj);
        if (empty($cnpj) || strlen($cnpj) != 14) {
            return false;
        } else {

                $sum = 0;
                $rev_cnpj = strrev(substr($cnpj, 0, 12));
                for ($i = 0; $i <= 11; $i++) {
                    $i == 0 ? $multiplier = 2 : $multiplier;
                    $i == 8 ? $multiplier = 2 : $multiplier;
                    $multiply = ($rev_cnpj[$i] * $multiplier);
                    $sum = $sum + $multiply;
                    $multiplier++;
                }
                $rest = $sum % 11;
                if ($rest == 0 || $rest == 1) {
                    $dv1 = 0;
                } else {
                    $dv1 = 11 - $rest;
                    $sub_cnpj = substr($cnpj, 0, 12);
                    $rev_cnpj = strrev($sub_cnpj . $dv1);
                    unset($sum);
                    $sum = 0;
                    for ($i = 0; $i <= 12; $i++) {

                        $i == 0 ? $multiplier = 2 : $multiplier;
                        $i == 8 ? $multiplier = 2 : $multiplier;
                        $multiply = ($rev_cnpj[$i] * $multiplier);
                        $sum = $sum + $multiply;
                        $multiplier++;
                    }
                    $rest = $sum % 11;
                    if ($rest == 0 || $rest == 1) {
                        $dv2 = 0;
                    } else {
                        $dv2 = 11 - $rest;
                    }

                    if ($dv1 == $cnpj[12] && $dv2 == $cnpj[13]) {
                        return true;
                    } else {
                        return false;
                    }
                }

        }
    }*/


    public static function traduzirDataBanco($data)
    {
        $retorno = str_replace('day', 'dia', $data);
        $retorno = str_replace('days', 'dias', $retorno);
        $retorno = str_replace('week', 'semana', $retorno);
        $retorno = str_replace('weeks', 'semanas', $retorno);
        $retorno = str_replace('month', 'mês', $retorno);
        $retorno = str_replace('months', 'meses', $retorno);
        $retorno = str_replace('hour', 'hora', $retorno);
        $retorno = str_replace('hours', 'horas', $retorno);
        return $retorno;
    }

    public static function formatarMoedaSefaz($numero)
    {
        return sprintf('%.4f', $numero / 10000);
    }
}
