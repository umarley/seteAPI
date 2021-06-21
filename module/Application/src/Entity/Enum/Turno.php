<?php

namespace Db\Enum;


class Turno {
    
    const MATUTINO = 1;
    const VESPERTINO = 2;
    const INTEGRAL = 3;
    const NOTURNO = 4;
    
    const TURNO = [
        self::MATUTINO,
        self::VESPERTINO,
        self::INTEGRAL,
        self::NOTURNO
    ];
    
    public function getLabel($valor){
        switch ($valor){
            case self::MATUTINO:
                return "Matutino";
                break;
            case self::VESPERTINO:
                return "Vespertino";
                break;
            case self::INTEGRAL:
                return "Integral";
                break;
            case self::NOTURNO:
                return "Noturno";
                break;
        }
    }
    

}
