<?php

namespace Db\Enum;


class MecTpLocalizacaoDiferenciada {
    
    const AREA_ASSENTAMENTO = 1;
    const TERRA_INDIGENA = 2;
    const AREA_REMANESCENTE_QUILOMBO = 3;
    const NAO_SE_APLICA = 7;
    
    const LOCALIZACAO_DIFERENCIADA = [
        self::AREA_ASSENTAMENTO,
        self::TERRA_INDIGENA,
        self::AREA_REMANESCENTE_QUILOMBO,
        self::NAO_SE_APLICA
    ];
    
    public function getLabelSexo($valor){
        switch ($valor){
            case self::AREA_ASSENTAMENTO:
                return "Área de Assentamento";
                break;
            case self::TERRA_INDIGENA:
                return "Terra Indígena";
                break;
            case self::AREA_REMANESCENTE_QUILOMBO:
                return "Área remanescente de Quilombo";
                break;
            case self::NAO_SE_APLICA:
                return "Não se aplica";
                break;
        }
    }
    

}
