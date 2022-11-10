<?php

namespace Sete\V1\Rest\Chamados;


class ChamadosModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Application\Utils\Rest("https://suporte.transportesufg.eng.br/os-ticket/api/tickets.json", []);
    }
    
    public function validarAberturaOS($arOS){
        $boValidate = true;
        $arErros = [];
        if(!isset($arOS['nome']) || empty($arOS['nome'])){
            $boValidate = false;
            $arErros['nome'] = "O Campo nome é obrigatório";
        }
        if(!isset($arOS['telefone']) || empty($arOS['telefone'])){
            $boValidate = false;
            $arErros['telefone'] = "O Campo telefone é obrigatório";
        }
        if(!isset($arOS['email']) || empty($arOS['email'])){
            $boValidate = false;
            $arErros['email'] = "O Campo email é obrigatório";
        }else if(!\Application\Utils\Utils::validarEmail($arOS['email'])){
            $boValidate = false;
            $arErros['email'] = "O Campo email informado é inválido";
        }
        if(!isset($arOS['titulo_problema']) || empty($arOS['titulo_problema'])){
            $boValidate = false;
            $arErros['titulo_problema'] = "O Campo titulo_problema é obrigatório";
        }
        if(!isset($arOS['mensagem']) || empty($arOS['mensagem'])){
            $boValidate = false;
            $arErros['mensagem'] = "O Campo mensagem é obrigatório";
        }
        if(!isset($arOS['plataforma']) || empty($arOS['plataforma'])){
            $boValidate = false;
            $arErros['plataforma'] = "A plataforma deve ser informada";
        }
        
        return ['result' => $boValidate, 'messages' => $arErros];
        
    }
    
    private function getTemplateAberturaOSTicket($codigoCidade, $arOS){
        $dbGlbMunicipio = new \Db\SetePG\GlbMunicipios();
        $arDadosLocal = $dbGlbMunicipio->getByCodigo($codigoCidade);
        return [
            'alert' => true,
            'autorespond' => true,
            'source' => 'API',
            'name' => $arOS['nome'],
            'email' => $arOS['email'],
            'phone' => $arOS['telefone'],
            'subject' => $arOS['titulo_problema'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'message' => "data:text/html, " . $arOS['mensagem'],
            'estado' => $arDadosLocal['estado'],
            'cidade' => $arDadosLocal['nm_cidade'],
            'plataforma' => $arOS['plataforma'],
            'attachments' => $arOS['anexos']
        ];
        
    }
    
    public function abrirChamadoOsTicket($codigoCidade, $arPost){
        
        $this->_entity->setParametros($this->getTemplateAberturaOSTicket($codigoCidade, $arPost));
        $this->_entity->setHeader(['X-API-Key' => '35CEC665A81307DA1F27AAE8E8874771']);
        $numeroChamado = $this->_entity->post(true)->getResposta();
        $boDeuCerto = true;
        if(is_numeric($numeroChamado)){
            $messages = "Chamado {$numeroChamado} criado com sucesso!";
        }else{
            $boDeuCerto = false;
            $messages = $numeroChamado;
        }
        
        return ['result' => $boDeuCerto, 'messages' => $messages];
        
    }
    
    

}
