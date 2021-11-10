<?php

namespace Db\Enum;


class ModoVeiculo {
    
    const RODOVIARIO = 0;
    const AQUAVIARIO = 1;
    
    const MODO_VEICULO = [
        self::RODOVIARIO,
        self::AQUAVIARIO,
    ];
    
    public static function getLabel($valor){
        switch ($valor){
            case self::RODOVIARIO:
                return "Rodoviário";
                break;
            case self::AQUAVIARIO:
                return "Aquaviário";
                break;
        }
    }
    

}
