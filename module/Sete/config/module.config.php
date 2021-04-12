<?php
return [
    'service_manager' => [
        'factories' => [
            \Sete\V1\Rest\User\UserResource::class => \Sete\V1\Rest\User\UserResourceFactory::class,
            \Sete\V1\Rest\Authenticator\AuthenticatorResource::class => \Sete\V1\Rest\Authenticator\AuthenticatorResourceFactory::class,
            \Sete\V1\Rest\Municipios\MunicipiosResource::class => \Sete\V1\Rest\Municipios\MunicipiosResourceFactory::class,
            \Sete\V1\Rest\PermissaoFirebase\PermissaoFirebaseResource::class => \Sete\V1\Rest\PermissaoFirebase\PermissaoFirebaseResourceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'sete.rest.user' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/users[/:user_type[/:user_id]]',
                    'defaults' => [
                        'controller' => 'Sete\\V1\\Rest\\User\\Controller',
                    ],
                ],
            ],
            'sete.rest.authenticator' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/authenticator',
                    'defaults' => [
                        'controller' => 'Sete\\V1\\Rest\\Authenticator\\Controller',
                    ],
                ],
            ],
            'sete.rest.municipios' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/municipios[/:municipios_id]',
                    'defaults' => [
                        'controller' => 'Sete\\V1\\Rest\\Municipios\\Controller',
                    ],
                ],
            ],
            'sete.rest.permissao-firebase' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/permissao-firebase[/:permissao_firebase_id]',
                    'defaults' => [
                        'controller' => 'Sete\\V1\\Rest\\PermissaoFirebase\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'api-tools-versioning' => [
        'uri' => [
            0 => 'sete.rest.user',
            1 => 'sete.rest.authenticator',
            2 => 'sete.rest.municipios',
            3 => 'sete.rest.permissao-firebase',
        ],
    ],
    'api-tools-rest' => [
        'Sete\\V1\\Rest\\User\\Controller' => [
            'listener' => \Sete\V1\Rest\User\UserResource::class,
            'route_name' => 'sete.rest.user',
            'route_identifier_name' => 'user_id',
            'collection_name' => 'user',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PUT',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
                2 => 'PUT',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \Sete\V1\Rest\User\UserEntity::class,
            'collection_class' => \Sete\V1\Rest\User\UserCollection::class,
            'service_name' => 'User',
        ],
        'Sete\\V1\\Rest\\Authenticator\\Controller' => [
            'listener' => \Sete\V1\Rest\Authenticator\AuthenticatorResource::class,
            'route_name' => 'sete.rest.authenticator',
            'route_identifier_name' => 'authenticator_id',
            'collection_name' => 'authenticator',
            'entity_http_methods' => [],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \Sete\V1\Rest\Authenticator\AuthenticatorEntity::class,
            'collection_class' => \Sete\V1\Rest\Authenticator\AuthenticatorCollection::class,
            'service_name' => 'Authenticator',
        ],
        'Sete\\V1\\Rest\\Municipios\\Controller' => [
            'listener' => \Sete\V1\Rest\Municipios\MunicipiosResource::class,
            'route_name' => 'sete.rest.municipios',
            'route_identifier_name' => 'municipios_id',
            'collection_name' => 'municipios',
            'entity_http_methods' => [
                0 => 'GET',
            ],
            'collection_http_methods' => [
                0 => 'GET',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \Sete\V1\Rest\Municipios\MunicipiosEntity::class,
            'collection_class' => \Sete\V1\Rest\Municipios\MunicipiosCollection::class,
            'service_name' => 'Municipios',
        ],
        'Sete\\V1\\Rest\\PermissaoFirebase\\Controller' => [
            'listener' => \Sete\V1\Rest\PermissaoFirebase\PermissaoFirebaseResource::class,
            'route_name' => 'sete.rest.permissao-firebase',
            'route_identifier_name' => 'permissao_firebase_id',
            'collection_name' => 'permissao_firebase',
            'entity_http_methods' => [
                0 => 'GET',
            ],
            'collection_http_methods' => [
                0 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \Sete\V1\Rest\PermissaoFirebase\PermissaoFirebaseEntity::class,
            'collection_class' => \Sete\V1\Rest\PermissaoFirebase\PermissaoFirebaseCollection::class,
            'service_name' => 'PermissaoFirebase',
        ],
    ],
    'api-tools-content-negotiation' => [
        'controllers' => [
            'Sete\\V1\\Rest\\User\\Controller' => 'Json',
            'Sete\\V1\\Rest\\Authenticator\\Controller' => 'Json',
            'Sete\\V1\\Rest\\Municipios\\Controller' => 'HalJson',
            'Sete\\V1\\Rest\\PermissaoFirebase\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'Sete\\V1\\Rest\\User\\Controller' => [
                0 => 'application/vnd.sete.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'Sete\\V1\\Rest\\Authenticator\\Controller' => [
                0 => 'application/vnd.sete.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'Sete\\V1\\Rest\\Municipios\\Controller' => [
                0 => 'application/vnd.sete.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'Sete\\V1\\Rest\\PermissaoFirebase\\Controller' => [
                0 => 'application/vnd.sete.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'Sete\\V1\\Rest\\User\\Controller' => [
                0 => 'application/vnd.sete.v1+json',
                1 => 'application/json',
            ],
            'Sete\\V1\\Rest\\Authenticator\\Controller' => [
                0 => 'application/vnd.sete.v1+json',
                1 => 'application/json',
            ],
            'Sete\\V1\\Rest\\Municipios\\Controller' => [
                0 => 'application/vnd.sete.v1+json',
                1 => 'application/json',
            ],
            'Sete\\V1\\Rest\\PermissaoFirebase\\Controller' => [
                0 => 'application/vnd.sete.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'api-tools-hal' => [
        'metadata_map' => [
            \Sete\V1\Rest\User\UserEntity::class => [
                'entity_identifier_name' => 'type, id',
                'route_name' => 'sete.rest.user',
                'route_identifier_name' => 'user_id',
                'hydrator' => \Laminas\Hydrator\ArraySerializable::class,
            ],
            \Sete\V1\Rest\User\UserCollection::class => [
                'entity_identifier_name' => 'type, id',
                'route_name' => 'sete.rest.user',
                'route_identifier_name' => 'user_id',
                'is_collection' => true,
            ],
            \Sete\V1\Rest\Authenticator\AuthenticatorEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'sete.rest.authenticator',
                'route_identifier_name' => 'authenticator_id',
                'hydrator' => \Laminas\Hydrator\ArraySerializable::class,
            ],
            \Sete\V1\Rest\Authenticator\AuthenticatorCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'sete.rest.authenticator',
                'route_identifier_name' => 'authenticator_id',
                'is_collection' => true,
            ],
            \Sete\V1\Rest\Municipios\MunicipiosEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'sete.rest.municipios',
                'route_identifier_name' => 'municipios_id',
                'hydrator' => \Laminas\Hydrator\ArraySerializable::class,
            ],
            \Sete\V1\Rest\Municipios\MunicipiosCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'sete.rest.municipios',
                'route_identifier_name' => 'municipios_id',
                'is_collection' => true,
            ],
            \Sete\V1\Rest\PermissaoFirebase\PermissaoFirebaseEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'sete.rest.permissao-firebase',
                'route_identifier_name' => 'permissao_firebase_id',
                'hydrator' => \Laminas\Hydrator\ArraySerializable::class,
            ],
            \Sete\V1\Rest\PermissaoFirebase\PermissaoFirebaseCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'sete.rest.permissao-firebase',
                'route_identifier_name' => 'permissao_firebase_id',
                'is_collection' => true,
            ],
        ],
    ],
    'api-tools-content-validation' => [
        'Sete\\V1\\Rest\\Authenticator\\Controller' => [
            'input_filter' => 'Sete\\V1\\Rest\\Authenticator\\Validator',
        ],
    ],
    'input_filter_specs' => [
        'Sete\\V1\\Rest\\Authenticator\\Validator' => [
            0 => [
                'required' => true,
                'validators' => [],
                'filters' => [],
                'name' => 'usuario',
                'description' => 'Campo que deve ser informado o usuário para o login no sistema.',
                'error_message' => 'Preencha o campo usuário para continuar!',
            ],
            1 => [
                'required' => true,
                'validators' => [],
                'filters' => [],
                'name' => 'senha',
                'description' => 'Campo com a senha do usuário. Deve ser enviado o hash md5 da senha informada pelo usuário do sistema.',
                'error_message' => 'Campo obrigatório!',
            ],
        ],
    ],
    'api-tools-mvc-auth' => [
        'authorization' => [
            'Sete\\V1\\Rest\\Authenticator\\Controller' => [
                'collection' => [
                    'GET' => false,
                    'POST' => false,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ],
                'entity' => [
                    'GET' => false,
                    'POST' => false,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ],
            ],
        ],
    ],
];
