<?php

namespace Db\Enum;

class TipoCombustivel {

    const GASOLINA = 'G';
    const DIESEL = 'D';
    const ETANOL = 'E';
    const GAS_NATURAL = 'N';
    const OUTRO = 'O';
    const TIPO_COMBUSTIVEL = [
        self::GASOLINA,
        self::DIESEL,
        self::ETANOL,
        self::GAS_NATURAL,
        self::OUTRO
    ];

    public static function getLabel($valor) {
        switch ($valor) {
            case self::GASOLINA:
                return "Gasolina";
                break;
            case self::DIESEL:
                return "Diesel";
                break;
            case self::ETANOL:
                return "Etanol";
                break;
            case self::GAS_NATURAL:
                return "Gás Natural";
                break;
            case self::OUTRO:
                return "Outro";
                break;
        }
    }

    public static function getLista() {
        return [
            ['id' => self::GASOLINA, 'tipo' => 'Gasolina'],
            ['id' => self::DIESEL, 'tipo' => 'Diesel'],
            ['id' => self::ETANOL, 'tipo' => 'Etanol'],
            ['id' => self::GAS_NATURAL, 'tipo' => 'Gás Natural'],
            ['id' => self::OUTRO, 'tipo' => 'Outro']
        ];
    }

}
