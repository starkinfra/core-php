<?php

namespace StarkCore;
use StarkCore\Utils\Checks;


class PublicUser
{
    function __construct($environment)
    {
        $this->environment = Checks::checkEnvironment($environment);
    }
}
