<?php

namespace Db\Enum;


class Origem {
    
    const PROPRIO= 1;
    const TERCEIRIZADO = 2;
    
    const ORIGEM = [
        self::PROPRIO,
        self::TERCEIRIZADO,
    ];
    
    public function getLabel($valor){
        switch ($valor){
            case self::PROPRIO:
                return "Próprio";
                break;
            case self::TERCEIRIZADO:
                return "Terceirizado";
                break;
        }
    }
    

}
