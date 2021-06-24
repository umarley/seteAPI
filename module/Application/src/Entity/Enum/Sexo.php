<?php

namespace Db\Enum;

class Sexo {

    const SEXO_MASCULINO = 1;
    const SEXO_FEMININO = 2;
    const SEXO_NAO_INFORMADO = 3;
    const SEXOS = [
        self::SEXO_MASCULINO,
        self::SEXO_FEMININO,
        self::SEXO_NAO_INFORMADO
    ];

    public function getLabelSexo($sexo) {
        switch ($sexo) {
            case self::SEXO_MASCULINO:
                return "Masculino";
                break;
            case self::SEXO_FEMININO:
                return "Feminino";
                break;
            case self::SEXO_NAO_INFORMADO:
                return "Não Informado";
                break;
        }
    }

    public static function sexos() {
        return [
            self::SEXO_MASCULINO,
            self::SEXO_FEMININO,
            self::SEXO_NAO_INFORMADO
        ];
    }

}
