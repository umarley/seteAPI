<?php

namespace Db\Sistema;

use Db\Core\AbstractDatabasePostgres;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

class RecuperarSenha extends AbstractDatabasePostgres {

    public function __construct() {
        $this->table = 'recuperar_senha';
        $this->primaryKey = 'id_recuperacao';
        $this->schema = 'sistema';
        parent::__construct(AbstractDatabasePostgres::DATABASE_CORE);
    }

    public function getById($arIds) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("codigo_cidade = {$arIds['codigo_cidade']} AND id_rota = {$arIds['id_rota']}");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $row = $prepare->execute()->current();
        return $row;
    }
    
    public function gerarNovoToken($codigoCidade, $email){
        $idUsuario = $this->getIdUsuario($email);
        $tokenGerado = uniqid() . md5($email);
        $arResult = $this->_inserir([
            'token' => $tokenGerado,
            'id_usuario' => $idUsuario,
            'dt_criacao' => date("Y-m-d H:i:s"),
            'is_usado' => 'N',
            'codigo_cidade' => $codigoCidade
        ]);
        //CONTINUAR AQUI
        /*
         * $arDadosMensagem['emailFrom']: Email origem da mensagem \r\n
     * $arDadosMensagem['emailTo']: Email do destinatário qual o email será enviado \r\n
     * $arDadosMensagem['emailAssunto']: Assunto do Email a ser enviado
     * $arDadosMensagem['emailMensagem']: Mensagem do Email a ser enviado
         */
        if($arResult['result']){
            $dbSystem = new \Db\Core\System();
            $dbSystem->enviarEmail([
                'tituloRemetente' => "CECATE",
                'emailFrom' => 'cecateufg@gmail.com',
                'emailTo' => $email,
                'emailAssunto' => 'Recuperação de Acesso - SETE',
                'emailMensagem' => "Segue o seu código de recuperação de senha: \r\n {$tokenGerado} \r\n Copie e cole no SETE para continuar a redefinição de sua senha."
            ]);
            return ['result' => true, 'messages' => "Código de redefinição de senha enviado para o seu email."];
        }else{
            return $arResult;
        }
    }
    
    private function getIdUsuario($email){
        $dbSeteUsuarios = new \Db\SetePG\SeteUsuarios();
        $idUsuario = $dbSeteUsuarios->getIdUsuarioByUsername($email);
        return $idUsuario;
    }
    
    private function getLinkRecuperacaoSenha($token){
        $urlHelper = new \Application\Utils\UrlHelper();
        $link = $urlHelper->baseUrl("web/recuperar-senha");
        $link .= "?token={$token}";
        return $link;
    }

}
