<?php

namespace Db\Enum;


class MecTpLocalizacao {
    
    const URBANA = 1;
    const RURAL = 2;
    
    const SEXOS = [
        self::SEXO_MASCULINO,
        self::SEXO_FEMININO
    ];
    
    public function getLabelSexo($valor){
        switch ($valor){
            case self::URBANA:
                return "Urbana";
                break;
            case self::RURAL:
                return "Rural";
                break;
        }
    }
    

}
