<?php

namespace Db\Enum;


class GrauParentesco {
    
    const PAI_MAE_PADRASTO_MADRASTA = 0;
    const AVOS = 1;
    const IRMA_IRMAO = 2;
    const OUTRO_PARENTE = 4;
    
    const GRAU_PARENTESCO = [
        self::PAI_MAE_PADRASTO_MADRASTA,
        self::AVOS,
        self::IRMA_IRMAO,
        self::OUTRO_PARENTE
    ];
    
    public function getLabel($valor){
        switch ($valor){
            case self::PAI_MAE_PADRASTO_MADRASTA:
                return "Infantil";
                break;
            case self::AVOS:
                return "Fundamental";
                break;
            case self::IRMA_IRMAO:
                return "Médio";
                break;
            case self::OUTRO_PARENTE:
                return "Superior";
                break;
        }
    }
    

}
