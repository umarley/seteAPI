<?php

namespace Db\Enum;


class MecTpDependencia {
    //1. Federal 2. Estadual 3. Municipal 4. Privada
    const FEDERAL = 1;
    const ESTADUAL = 2;
    const MUNICIPAL = 3;
    const PRIVADA = 4;
    
    const DEPENDENCIA = [
        self::FEDERAL,
        self::ESTADUAL,
        self::MUNICIPAL,
        self::PRIVADA
    ];
    
    public function getLabel($valor){
        switch ($valor){
            case self::FEDERAL:
                return "Federal";
                break;
            case self::ESTADUAL:
                return "Estadual";
                break;
            case self::MUNICIPAL:
                return "Municipal";
                break;
            case self::PRIVADA:
                return "Privada";
                break;
        }
    }
    

}
