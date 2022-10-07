<?php

namespace Sete\V1\Rest\Chamados;


class ChamadosModel {

    protected $_entity;

    public function __construct() {
        $this->_entity = new \Application\Utils\Rest("https://suporte.transportesufg.eng.br/os-ticket/api/tickets.json", []);
    }
    
    /**
     * {
    "alert": true,
    "autorespond": true,
    "source": "API",
    "name": "Angry User",
    "email": "umarleyricardo@gmail.com",
    "phone": "3185558634X123",
    "subject": "Testing API",
    "ip": "123.211.233.122",
    "message": "data:text/html,MESSAGE <b>HERE</b>",
    "estado": "Goiás",
    "cidade": "Aparecida de Goiânia",
    "plataforma": "SETE WEB",
    "attachments": [
        {"file.txt": "data:text/plain;charset=utf-8,content"},
        {"image.png": "data:image/png;base64,R0lGODdhMAA..."}
    ]
}
     */
    
    

}
