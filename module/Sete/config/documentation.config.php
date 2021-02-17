<?php

return [
    'Sete\\V1\\Rest\\Authenticator\\Controller' => [
        'description' => 'Serviço de autenticação',
        'collection' => [
            'POST' => [
                'response' => '{
    "access_token": {
        "access_token": "768795aa779a125903487ac48a78bc3d7b094714-web@sete.com.br",
        "expires_in": 10800
    },
    "messages": "Login efetuado com sucesso!"
}',
                'request' => '{
  "usuario": "string",
    "senha": "string"
}',
            ],
            'description' => 'Obtém o retorno se o access_token é válido.

O Access Token deve ser enviado no Header da solicitação:
curl -X GET --header \'access_token: 2d3a79917740293d32cbe40f94f1826940dcaf0b-web@sete.com.br\' \'http://<url_api>/authenticator\'":',
            'GET' => [
                'response' => '{
    "result": true,
    "messages": "Access Token válido!"
}


{
    "result": false,
    "messages": "Access Token inválido!"
}',
            ],
        ],
        'entity' => [
            'description' => 'Obtém o retorno se o access_token é válido.

O Access Token deve ser enviado no Header da solicitação:
curl -X GET --header \'access_token: 2d3a79917740293d32cbe40f94f1826940dcaf0b-web@sete.com.br\' \'http://<url_api>/authenticator\'":',
        ],
    ],
];
