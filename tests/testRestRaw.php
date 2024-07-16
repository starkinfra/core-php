<?php

namespace Test\RestGet;
use Exception;
use DateTime;

use Test\Utils\User;
use StarkCore\Utils\Rest;
use StarkCore\Utils\Checks;
use StarkCore\Utils\Resource;
use StarkCore\Utils\StarkHost;


class TestRestRaw
{    
    public function testGetAndPatchSuccess()
    {
        $request = Rest::getRaw(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            "pt-BR",
            15,
            "invoice",
            null,
            ["limit" => 1, "status" => "paid"],
            "Joker",
            false
        )->content;

        $testAssertion = json_decode($request, true);
        if (!is_int($testAssertion["invoices"][0]["amount"])) {
          throw new Exception("get failed");
        }
     
        $body = ["amount" => 0];
        $path = "invoice/" . $testAssertion["invoices"][0]["id"];

        $request = Rest::patchRaw(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            "pt-BR",
            15,
            $path,
            $body,
            null,
            "Joker",
            false
        )->content;
        $testAssertion = json_decode($request, true);
        if ($testAssertion["message"] != "Fatura(s) atualizada(s) com sucesso") {
          throw new Exception("patch failed");
        }
    }

    public function testPostAndDeleteSuccess()
    {
        $timestamp = time();
        $date = new DateTime();
        $date->modify('+10 days');
    
        $path = "transfer/";
        $body = [
            "transfers" => [
                [
                    "amount" => 10000,
                    "name" => "Steve Rogers",
                    "taxId" => "330.731.970-10",
                    "bankCode" => "001",
                    "branchCode" => "1234",
                    "accountNumber" => "123456-0",
                    "accountType" => "checking",
                    "scheduled" => $date->format('Y-m-d'),
                    "externalId" => (string) $timestamp,
                ]
            ]
        ];
    
        $request = Rest::postRaw(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            "pt-BR",
            15,
            $path,
            $body,
            null,
            "Joker",
            false
        )->content;

        $testAssertion = json_decode($request, true);
        if (!is_int($testAssertion["transfers"][0]["amount"])) {
          throw new Exception("failed");
        }

        $request = Rest::deleteRaw(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            "pt-BR",
            15,
            $path . $testAssertion["transfers"][0]["id"],
            null,
            null,
            "Joker",
            false
        )->content;

        $testAssertion = json_decode($request, true);
        if (!$testAssertion["transfer"]["status"] === "canceled") {
          throw new Exception("failed");
        }
    }

    public function putSuccess()
    {
        $path = "split-profile";
        $data = [
            "profiles" =>[
                [
                    "interval" => "day",
                    "delay" => 0
                ]
            ]
        ];
        
        $request = Rest::putRaw(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            "pt-BR",
            15,
            $path,
            $data,
            null,
            "Joker",
            false
        )->content;

        $testAssertion = json_decode($request, true);
        if (!$testAssertion["profiles"][0]["delay"] === 0) {
            throw new Exception("failed");
        }

    }

}

echo "\n\nRestRaw:";
$tests = new TestRestRaw();
echo "\n- success";

echo "\n\ngetAndPatchRaw:";
$tests->testGetAndPatchSuccess();
echo " - OK";

echo "\n\npostAndDeleteRaw:";
$tests->testPostAndDeleteSuccess();
echo " - OK";

echo "\n\nputRaw:";
$tests->putSuccess();
echo " - OK";