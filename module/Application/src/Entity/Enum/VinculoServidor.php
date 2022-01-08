<?php

namespace Db\Enum;

class VinculoServidor {

    const EFETIVO = 1;
    const COMISSIONADO = 2;
    const TERCEIRO = 3;
    const OUTRO = 4;
    const VINCULOS = [
        self::EFETIVO,
        self::COMISSIONADO,
        self::TERCEIRO,
        self::OUTRO
    ];

    public function getLabelVinculo($sexo) {
        switch ($sexo) {
            case self::EFETIVO:
                return "Servidor efetivo";
                break;
            case self::COMISSIONADO:
                return "Servidor comissionado";
                break;
            case self::TERCEIRO:
                return "Terceirizado";
                break;
            case self::OUTRO:
                return "Outro";
                break;
        }
    }

    public static function vinculos() {
        return [
            self::EFETIVO,
            self::COMISSIONADO,
            self::TERCEIRO,
            self::OUTRO
        ];
    }

}
