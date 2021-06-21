<?php

namespace Db\Enum;


class NivelAluno {
    
    const INFANTIL = 1;
    const FUNDAMENTAL = 2;
    const MEDIO = 3;
    const SUPERIOR = 4;
    const OUTRO = 5;
    
    const NIVEL = [
        self::INFANTIL,
        self::FUNDAMENTAL,
        self::MEDIO,
        self::SUPERIOR,
        self::OUTRO
    ];
    
    public function getLabel($valor){
        switch ($valor){
            case self::INFANTIL:
                return "Infantil";
                break;
            case self::FUNDAMENTAL:
                return "Fundamental";
                break;
            case self::MEDIO:
                return "Médio";
                break;
            case self::SUPERIOR:
                return "Superior";
                break;
            case self::OUTRO:
                return "Outro";
                break;
        }
    }
    

}
