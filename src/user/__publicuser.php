<?php

namespace StarkCore;
use StarkCore\Utils\Checks;


class PublicUser
{
    public $environment;

    function __construct($environment)
    {
        $this->environment = Checks::checkEnvironment($environment);
    }
}
