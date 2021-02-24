<?php

namespace Application\Utils\Decorator\Form;


class Validator
{



    public function __construct()
    {

    }

    public static function showMessages(Array $messages, $class = 'warning'){

        $html = "<div class=\"row\">
                    <div class=\"col-sm-12 col-md-12\">
                        <div class=\"alert alert-{$class} alert-dismissible\" role=\"alert\">
                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>";
                            foreach (@$messages as $message){
                              $html .= "<i class=\"fa fa-warning\"></i> $message <br />";
                             }
                     $html .= "   </div>
                    </div>
                </div>";
        echo $html;
    }






}