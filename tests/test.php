<?php

require_once("vendor/autoload.php");
require_once("utils/user.php");

echo "\n\nStarting tests\n";

include_once("testRestGet.php");
include_once("testRestRaw.php");

echo "\n\nAll tests concluded\n\n";
