<?php

namespace Db\Enum;

class TipoVeiculo {

    const ONIBUS = 1;
    const MICRO_ONIBUS = 2;
    const VAN = 3;
    const KOMBI = 4;
    const CAMINHAO = 5;
    const CAMINHONETE = 6;
    const MOTOCICLETA = 7;
    const ANIMAL_DE_TRACAO = 8;
    const LANCHA_VOADEIRA = 9;
    const BARCO_DE_MADEIRA = 10;
    const BARCO_DE_ALUMINIO = 11;
    const CANOA_MOTORIZADA = 12;
    const CANOA_A_REMO = 13;
    const BICICLETA = 14;
    const OUTRO = 99;
    const TIPO_VEICULO = [
        self::ONIBUS,
        self::MICRO_ONIBUS,
        self::VAN,
        self::KOMBI,
        self::CAMINHAO,
        self::CAMINHONETE,
        self::MOTOCICLETA,
        self::ANIMAL_DE_TRACAO,
        self::LANCHA_VOADEIRA,
        self::BARCO_DE_MADEIRA,
        self::BARCO_DE_ALUMINIO,
        self::CANOA_MOTORIZADA,
        self::CANOA_A_REMO,
        self::BICICLETA,
        self::OUTRO
    ];

    public static function getLabel($valor) {
        switch ($valor) {
            case self::ONIBUS:
                return "Ônibus";
                break;
            case self::MICRO_ONIBUS:
                return "Micro-Ônibus";
                break;
            case self::VAN:
                return "Van";
                break;
            case self::KOMBI:
                return "Kombi";
                break;
            case self::CAMINHAO:
                return "Caminhão";
                break;
            case self::CAMINHONETE:
                return "Caminhonete";
                break;
            case self::MOTOCICLETA:
                return "Motocicleta";
                break;
            case self::ANIMAL_DE_TRACAO:
                return "Animal de tração";
                break;
            case self::LANCHA_VOADEIRA:
                return "Lancha/Voadeira";
                break;
            case self::BARCO_DE_MADEIRA:
                return "Barco de madeira";
                break;
            case self::BARCO_DE_ALUMINIO:
                return "Barco de alumínio";
                break;
            case self::CANOA_MOTORIZADA:
                return "Canoa motorizada";
                break;
            case self::CANOA_A_REMO:
                return "Canoa a remo";
                break;
            case self::BICICLETA:
                return "Bicicleta";
                break;
            case self::OUTRO:
                return "Outro";
                break;
        }
    }

    public static function getLista() {
        return [
            ['id' => self::ONIBUS, 'tipo' => 'Ônibus'],
            ['id' => self::MICRO_ONIBUS, 'tipo' => 'Micro-Ônibus'],
            ['id' => self::VAN, 'tipo' => 'Van'],
            ['id' => self::KOMBI, 'tipo' => 'Kombi'],
            ['id' => self::CAMINHAO, 'tipo' => 'Caminhão'],
            ['id' => self::CAMINHONETE, 'tipo' => 'Caminhonete'],
            ['id' => self::MOTOCICLETA, 'tipo' => 'Motocicleta'],
            ['id' => self::ANIMAL_DE_TRACAO, 'tipo' => 'Animal de tração'],
            ['id' => self::LANCHA_VOADEIRA, 'tipo' => 'Lancha/Voadeira'],
            ['id' => self::BARCO_DE_MADEIRA, 'tipo' => 'Barco de madeira'],
            ['id' => self::BARCO_DE_ALUMINIO, 'tipo' => 'Barco de alumínio'],
            ['id' => self::CANOA_MOTORIZADA, 'tipo' => 'Canoa motorizada'],
            ['id' => self::CANOA_A_REMO, 'tipo' => 'Canoa a remo'],
            ['id' => self::BICICLETA, 'tipo' => 'Bicicleta'],
            ['id' => self::OUTRO, 'tipo' => 'Bicicleta']
        ];
    }

}
