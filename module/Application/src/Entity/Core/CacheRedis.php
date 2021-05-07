<?php

namespace Db\Core;

use Laminas\Cache\StorageFactory;

class CacheRedis {
    
    
    public function criaCacheAdapter($ttl = 3600) {
        $cache = StorageFactory::factory([
                    'adapter' => [
                        'name' => 'redis', // tipo de cache.
                        'options' => [
                            'ttl' => $ttl, // tempo que a informação ficará mantida no cache 1 semana
                            'server' => [
                                'host' => '127.0.0.1', 
                                'port' => '6379', 
                                'timeout' => 10
                                ]
                            ]
                        ]
        ]);
        return $cache;
    }
    
    
    
    
    
}

