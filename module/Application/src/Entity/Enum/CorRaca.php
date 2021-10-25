<?php

namespace Db\Enum;


class CorRaca {
    
    const NAO_INFORMADA = 0;
    const AMARELO = 1;
    const BRANCO = 2;
    const INDIGENA = 3;
    const PARDO = 4;
    const PRETO = 5;
    
    const COR_RACA = [
        self::NAO_INFORMADA,
        self::AMARELO,
        self::BRANCO,
        self::INDIGENA,
        self::PARDO,
        self::PRETO
    ];
    
    public function getLabel($valor){
        switch ($valor){
            case self::NAO_INFORMADA:
                return "Não informado";
                break;
            case self::AMARELO:
                return "Amarelo";
                break;
            case self::BRANCO:
                return "Branco";
                break;
            case self::INDIGENA:
                return "Indigena";
                break;
            case self::PARDO:
                return "Pardo";
                break;
            case self::PRETO:
                return "Preto";
                break;
        }
    }
    

}
