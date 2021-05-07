<?php

namespace Db\Core;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Message;
use Laminas\Cache\StorageFactory;

class LogFirebase extends AbstractDatabase {

    public function __construct() {
        $this->table = 'sys_log_firebase';
        $this->primaryKey = 'colecao';
        parent::__construct(self::DATABASE_CORE);
    }

    

}
