<?php

namespace Db\Enum;

class TipoServico {

    const COMBUSTIVEL = 1;
    const LUBRIFICANTE = 2;
    const PAGSEGURO = 3;
    const MANPREVENTIVA = 4;
    const MANUTENCAO = 5;
    const TIPO_SERVICO = [
        self::COMBUSTIVEL,
        self::LUBRIFICANTE,
        self::PAGSEGURO,
        self::MANPREVENTIVA,
        self::MANUTENCAO,
    ];

    public static function getLabel($valor) {
        switch ($valor) {
            case self::COMBUSTIVEL:
                return "Combustível";
                break;
            case self::LUBRIFICANTE:
                return "Lubrificante";
                break;
            case self::PAGSEGURO:
                return "Pag. Seguro";
                break;
            case self::MANPREVENTIVA:
                return "Man. Preventiva";
                break;
            case self::MANUTENCAO:
                return "Manutenção";
                break;
        }
    }

    public static function getLista() {
        return [
            ['id' => self::COMBUSTIVEL, 'tipo_servico' => 'Combustível'],
            ['id' => self::LUBRIFICANTE, 'tipo_servico' => 'Lubrificante'],
            ['id' => self::PAGSEGURO, 'tipo_servico' => 'Pag. Seguro'],
            ['id' => self::MANPREVENTIVA, 'tipo_servico' => 'Man. Preventiva'],
            ['id' => self::MANUTENCAO, 'tipo_servico' => 'Manutenção'],
        ];
    }

}
