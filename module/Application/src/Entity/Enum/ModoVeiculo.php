<?php

namespace Db\Enum;


class ModoVeiculo {
    
    const RODOVIARIO = 1;
    const AQUAVIARIO = 2;
    
    const MODO_VEICULO = [
        self::RODOVIARIO,
        self::AQUAVIARIO,
    ];
    
    public function getLabel($valor){
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
