<?php

namespace Test\Utils;


class User
{    
    public static function exampleProject()
    {
        return new \StarkCore\Project([
            "environment" => "sandbox",
            "id" => $_SERVER["SANDBOX_ID"],  // "9999999999999999"
            "privateKey" => $_SERVER["SANDBOX_PRIVATE_KEY"],  // "-----BEGIN EC PRIVATE KEY-----\nMHQCAQEEIBEcEJZLk/DyuXVsEjz0w4vrE7plPXhQxODvcG1Jc0WToAcGBSuBBAAK\noUQDQgAE6t4OGx1XYktOzH/7HV6FBukxq0Xs2As6oeN6re1Ttso2fwrh5BJXDq75\nmSYHeclthCRgU8zl6H1lFQ4BKZ5RCQ==\n-----END EC PRIVATE KEY-----"
        ]);
    }

    public static function exampleOrganization()
    {
        return new \StarkCore\Organization([
            "environment" => "sandbox",
            "id" => $_SERVER["SANDBOX_ORGANIZATION_ID"],  // "9999999999999999"
            "privateKey" => $_SERVER["SANDBOX_ORGANIZATION_PRIVATE_KEY"],  // "-----BEGIN EC PRIVATE KEY-----\nMHQCAQEEIBEcEJZLk/DyuXVsEjz0w4vrE7plPXhQxODvcG1Jc0WToAcGBSuBBAAK\noUQDQgAE6t4OGx1XYktOzH/7HV6FBukxq0Xs2As6oeN6re1Ttso2fwrh5BJXDq75\nmSYHeclthCRgU8zl6H1lFQ4BKZ5RCQ==\n-----END EC PRIVATE KEY-----"
        ]);
    }
}
