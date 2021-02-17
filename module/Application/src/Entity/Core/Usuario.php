<?php

namespace Db\Core;

use Application\Utils\Ldap;
use Db\Core\AbstractDatabase;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Adapter\Adapter;


class Usuario extends AbstractDatabase {

    const SITUACAO_ATIVO = 'A';
    const SITUACAO_PENDENTE = 'P';
    const SITUACAO_INATIVO = 'I';
    
    public function __construct() {
        $this->table = 'usuarios';
        $this->primaryKey = 'id';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    public function getLista() {
        /*
         * SELECT us.id_usuario, ps.nm_pessoa, us.is_ativo, sgc.nome as segmento FROM sys_usuarios us
            LEFT JOIN glb_pessoa ps ON ps.id_pessoa = us.id_pessoa
            LEFT JOIN glb_segmento_cadastro sgc ON ps.id_segmento_cadastro = sgc.id_tipo
            LIMIT 500;
         */
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->columns(['*']);
        $prepare = $sql->prepareStatementForSqlObject($select);
        $this->getResultSet($prepare->execute());
        $arUsuarios = [];
        foreach ($this->resultSet as $row){
            $arUsuarios[] = $row;
        }
        return $arUsuarios;
        
    }

    public function getUsuariosActiveDirectory() {
        $container = new \Zend\Session\Container('Auth');
        $arAcesso['username'] = $container->usuario;
        $arAcesso['password'] = base64_decode($container->token);
        $ldap = new Ldap($arAcesso);
        $arLista = $ldap->getListaUsuarios($ldap::FILTRO_USUARIOS_ATIVOS);
        return $arLista;
    }
    
    public function getNomeUsuarioByUsername($username){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['nm_usuario'])
                ->where("usuario = '{$username}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row['nm_usuario'];
    }
    
    public function getUsuarioById($id){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->columns(['*'])
                ->join(['ps' => new TableIdentifier('glb_pessoa')], "ps.id_pessoa = us.id_pessoa", ['segmento' => 'id_segmento_cadastro', 'nm_pessoa', 'situacao'])
                ->where("id_usuario = '{$id}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row;
    }
    
    public function getUsuarioByUsername($username){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['*'])
                ->where("email = '{$username}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row;
    }
    
    public function getNomeUsuarioByEmail($email){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['nm_usuario'])
                ->where("email = '{$email}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row['nm_usuario'];
    }
    
    public function getIdUsuarioByUsername($usuario){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id'])
                ->where("email = '{$usuario}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        return $row['id'];
    }
    
    public function usuarioIsTrocarSenha($usuario){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['trocar_senha'])
                ->where("usuario = '{$usuario}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        if($row['trocar_senha'] === \Db\SIR\Config::SIM){
            return true;
        }else{
            return false;
        }
    }
    
    public static function trataNomeParaExibirSistema($nomeUsuario){
        $parts = explode(" ", $nomeUsuario);
        $nomeExibir = $parts[0];
        if(isset($parts[1]) && !empty($parts[1])){
            $nomeExibir .= " {$parts[1]}";
        }
        if(isset($parts[2]) && !empty($parts[2]) && strlen($parts[1]) <= 2){
            $nomeExibir .= " {$parts[2]}";
        }
        return $nomeExibir;
    }
    
    public function usuarioExiste($email){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_usuario'])
                ->where("email = '{$email}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        if($execute->count() > 0){
            return true;
        }else{
            return false;
        }
    }
    
    public function getIdUsuarioByEmail($email){
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_usuario'])
                ->where("email = '{$email}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        $row = $execute->current();
        if($row) {
            return $row['id_usuario'];
        }
        return false;
    }

    public function getUsuarioByUsernameLDAP($username) {
        $container = new \Zend\Session\Container('Auth');
        $arAcesso['username'] = $container->usuario;
        $arAcesso['password'] = base64_decode($container->token);
        $ldap = new Ldap($arAcesso);
        $arLista = $ldap->getListaUsuarios($ldap::FILTRO_POR_USUARIO, $username);
        return $arLista[0];
    }

    public function getSuperUsuario($username) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['usuario'])
                ->where("usuario = '{$username}'")
                ->where("is_administrador = 'S'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        if ($execute->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function processaRecuperarSenha($email){
        $boSucesso = false;
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_usuario','usuario','id_usuario'])
                ->where("email = '{$email}'")
                ->where("is_ativo = 1");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        if ($execute->count() > 0) {
            $boSucesso = true;
            $dataSolicitacao = date("Y-m-d H:i:s");
            $token = md5($dataSolicitacao . $email);
            $arRow = $execute->current();
            $dbSirRecuperarSenha = new \Db\SIR\RecuperarSenha();
            $arDados = ['id_usuario' => $arRow['id_usuario'],
                'dt_criacao' => $dataSolicitacao,
                'dt_validade' => date('Y-m-d H:i:s', strtotime("+24 hours", strtotime($dataSolicitacao))),
                'token' => $token];
            $dbSirRecuperarSenha->_inserir($arDados);
            $this->enviarEmail($email, $token);
            return $boSucesso;
        }else{
            return $boSucesso;
        }
    }

    public function confirmarCadastro($token) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select($this->tableIdentifier)
                ->columns(['id_usuario'])
                ->where("enable_token = '{$token}'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();

        if($execute->count() > 0) {
            $arrUser = $execute->current();
            $id = $arrUser['id_usuario'];
            $sql = new Sql($this->AdapterBD);
            $update = $sql->update($this->tableIdentifier);
            $update->set(['is_ativo' => 'S', 'enable_token' => '']);
            $update->where(['id_usuario' => $id]);
            $sqlBuild = $this->sql->buildSqlString($update);
            return $this->AdapterBD->query($sqlBuild, Adapter::QUERY_MODE_EXECUTE);
        }
        else {
            return false;
        }
    }
    
    public function _deleteUserByIdPessoa($id) {
        $this->sql = new Sql($this->AdapterBD);
        $delete = $this->sql->delete($this->tableIdentifier);
        $delete->where("id_pessoa = " . $id);
        $sql = $this->sql->buildSqlString($delete);
        try {
            $this->AdapterBD->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $boResultado = true;
            $message = "Registro excluido com sucesso!";
        } catch (\PDOException $zAdapterEx) {
            $boResultado = false;
            $message = "Falha ao excluir o registro. Contacte o administrador do sistema para maiores informações. <br />" . $zAdapterEx->getMessage();
        } catch (\Laminas\Db\Adapter\Exception\InvalidQueryException $zendDbExc) {
            $boResultado = false;
            $message = "Falha ao excluir o registro. Contacte o administrador do sistema para maiores informações. <br />" . $zendDbExc->getMessage();
        }
        return ['result' => $boResultado, 'messages' => $message];
    }
    
    public function _deleteUserByIdPessoaEmLote($arIds) {
        $this->sql = new Sql($this->AdapterBD);
        $delete = $this->sql->delete($this->tableIdentifier);
        $delete->where("id_pessoa IN (" . implode(", ", $arIds) .")");
        $sql = $this->sql->buildSqlString($delete);
        try {
            $this->AdapterBD->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $boResultado = true;
            $message = "Registro excluido com sucesso!";
        } catch (\PDOException $zAdapterEx) {
            $boResultado = false;
            $message = "Falha ao excluir o registro. Contacte o administrador do sistema para maiores informações. <br />" . $zAdapterEx->getMessage();
        } catch (\Laminas\Db\Adapter\Exception\InvalidQueryException $zendDbExc) {
            $boResultado = false;
            $message = "Falha ao excluir o registro. Contacte o administrador do sistema para maiores informações. <br />" . $zendDbExc->getMessage();
        }
        return ['result' => $boResultado, 'messages' => $message];
    }

    public function alterarSenha($id, $senha, $novaSenha, $confSenha){
        $usuario = $this->getUsuarioById($id);
        if(md5($senha) != $usuario['senha']){
            return ['result' => false, 'message' => 'Senha atual inválida'];
        }
        if($novaSenha != $confSenha) {
            return ['result' => false, 'message' => 'Senha e confirmação não são iguais'];
        }
        if (strlen($novaSenha) < 6) {
            return ['result' => false, 'message' => "A senha deve ter pelo menos 6 caracteres"];
        }
        if (!preg_match("#[0-9]+#", $novaSenha) || !preg_match("#[a-zA-Z]+#", $novaSenha)) {
            return ['result' => false, 'message' => "A senha deve conter letras e números"];
        }
        $sql = new Sql($this->AdapterBD);
        $update = $sql->update($this->tableIdentifier);
        $update->set(['senha' => md5($novaSenha)]);
        $update->where(['id_usuario' => $id]);
        $sqlBuild = $this->sql->buildSqlString($update);
        if($this->AdapterBD->query($sqlBuild, Adapter::QUERY_MODE_EXECUTE)) {
            return ['result' => true, 'message' => 'Senha alterada com sucesso'];
        }
        else{
            return ['result' => false, 'message' => 'Erro de acesso ao Banco de Dados'];
        }
        
    }
    
    private function enviarEmail($email, $token){
        $urlHelper = new \Application\Utils\UrlHelper();
        $transport = new SMTPTransport();
        $options   = new SmtpOptions([
            'name'              => 'redemobconsorcio.com.br',
            'host'              => 'mail.redemobconsorcio.com.br',
            'port'              => 587,
            'connection_class'  => 'login',
            'connection_config' => [
                'username' => 'workflow@redemobconsorcio.com.br',
                'password' => 'mob@work2016',
                'ssl'      => 'tls',
            ],
        ]);
        $transport->setOptions($options);
        $message = new Message();
        $message->addTo($email);
        $message->addFrom('workflow@redemobconsorcio.com.br', 'Portal Valor em Servir');
        $message->setSubject('Recuperação de Senha Portal Valor em Servir');
        $message->setBody("Segue o link para redefinir a sua senha. \r\n \r\n {$urlHelper->baseUrl('login/trocar-senha')}?email={$email}&token={$token}");
        $message->setEncoding('UTF-8');
        
        $transport->send($message);

    }
    
    public function checkUsuarioAndPasswordPortal($usuario, $pass) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->columns(['usuario', 'senha', 'email'])
                ->where("email = '{$usuario}'")
                ->where("senha = '{$pass}'")
                ->where("is_ativo = 'S'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        if ($execute->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkUsuarioAndPassword($usuario, $pass) {
        $sql = new Sql($this->AdapterBD);
        $select = $sql->select(['us' => $this->tableIdentifier])
                ->columns(['email', 'senha'])
                ->where("email = '{$usuario}'")
                ->where("senha = '{$pass}'")
                ->where("is_ativo = 'S'");
        $prepare = $sql->prepareStatementForSqlObject($select);
        $execute = $prepare->execute();
        if ($execute->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function find($username) {
        $container = new \Zend\Session\Container('Auth');
        $arAcesso['username'] = $container->usuario;
        $arAcesso['password'] = base64_decode($container->token);
        $ldap = new Ldap($arAcesso);
        $arLista = $ldap->getListaUsuarios($ldap::FILTRO_POR_USUARIO, $username);
        return $arLista;
    }

}
