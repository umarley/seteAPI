<?php
return [
    'db' => [
        'adapters' => [
            'sete_api' => [
                'database' => 'sete',
                'driver' => 'PDO_pgsql',
                'hostname' => 'localhost',
                'username' => 'postgres',
                'password' => 'postgres',
                'charset' => 'utf8'
            ],
        ],
    ],
];
