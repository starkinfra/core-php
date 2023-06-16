<?php

namespace StarkCore\Utils;
use StarkCore\Utils\SubResource;


class Resource extends SubResource
{

    public $id;

    function __construct(&$params)
    {
        $id = Checks::checkParam($params, "id");
        if (!is_null($id)) {
            $id = strval($id);
        }
        $this->id = $id;
    }
}
