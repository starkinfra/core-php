<?php

use StarkCore\Utils\API;

$cases = [
    "Log" => "logs",
    "Entity" => "entities",
    "Key" => "keys",
    "Balances" => "balances",
    "AiSpeech" => "speeches",
    "Wish" => "wishes",
    "Box" => "boxes",
    "AiMatch" => "matches",
    "Boss" => "boss",
    "Address" => "address",
    "Status" => "status",
    "TaxBox" => "boxes",
    "Dish" => "dishes",
];

$failures = 0;
foreach ($cases as $resourceName => $expected) {
    $actual = API::lastNamePlural($resourceName);
    if ($actual === $expected) {
        continue;
    }
    $failures++;
    echo "FAIL lastNamePlural($resourceName): expected $expected, got $actual\n";
}

echo "\n\nAPI:\n\t- lastNamePlural " . (count($cases) - $failures) . "/" . count($cases) . " cases - " . ($failures > 0 ? "FAILED" : "OK") . "\n";
if ($failures > 0) {
    throw new Exception("lastNamePlural failed");
}
