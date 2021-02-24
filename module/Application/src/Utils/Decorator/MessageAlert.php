<?php

namespace Application\Utils\Decorator;

class MessageAlert{


    public function __construct(Array $flashMessenger, $class)
    {
        $html = "<div class=\"alert alert-{$class} alert-dismissable\">
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
                    {$flashMessenger[0]}
                  </div>";
                    
                    print $html;
    }

}