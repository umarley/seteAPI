<?php
namespace Sete\V1\Rest\Authenticator;
use Db\Core\AbstractDatabase;

class AuthenticatorEntity extends AbstractDatabase
{
    
    public function __construct() {
        $this->table = 'sys_config';
        $this->primaryKey = 'variable';
        parent::__construct(AbstractDatabase::DATABASE_CORE);
    }
    
    
    
}
