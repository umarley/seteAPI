<?php

namespace Db\Enum\Rota;


class Tipo {
    
    const RODOVIARIA = 1;
    const AQUAVIARIO = 2;
    const MISTA = 3;
    
    const TIPO = [
        self::RODOVIARIA,
        self::AQUAVIARIO,
        self::MISTA
    ];
    
    public function getLabel($valor){
        switch ($valor){
            case self::RODOVIARIA:
                return "Rodoviária";
                break;
            case self::AQUAVIARIO:
                return "Aquaviária";
                break;
            case self::MISTA:
                return "Mista";
                break;
        }
    }
    

}
