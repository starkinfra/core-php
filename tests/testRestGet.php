<?php

namespace Test\RestGet;
use Exception;
use Test\Utils\User;
use StarkCore\Utils\Rest;
use StarkCore\Utils\Checks;
use StarkCore\Utils\Resource;
use StarkCore\Utils\SubResource;
use StarkCore\Utils\StarkHost;


class Transaction extends Resource
{
    function __construct(array $params)
    {
        parent::__construct($params);
        $this->amount = Checks::checkParam($params, "amount");
    }

    public static function resource()
    {
        $transaction = function ($array) {
            return new Transaction($array);
        };
        return [
            "name" => "Transaction",
            "maker" => $transaction,
        ];
    }
}

class Payment extends SubResource
{

  public $name;
  public $taxId;
  public $bankCode;
  public $branchCode;
  public $accountNumber;
  public $accountType;
  public $amount;
  public $endToEndId;
  public $method;

  function __construct(array $params)
  {
    $this->name = Checks::checkParam($params, "name");
    $this->taxId = Checks::checkParam($params, "taxId");
    $this->bankCode = Checks::checkParam($params, "bankCode");
    $this->branchCode = Checks::checkParam($params, "branchCode");
    $this->accountNumber = Checks::checkParam($params, "accountNumber");
    $this->accountType = Checks::checkParam($params, "accountType");
    $this->amount = Checks::checkParam($params, "amount");
    $this->endToEndId = Checks::checkParam($params, "endToEndId");
    $this->method = Checks::checkParam($params, "method");

    Checks::checkParams($params);
  }

  public static function subResource()
  {
    $payment = function ($array) {
      return new Payment($array);
    };
    return [
      "name" => "Payment",
      "maker" => $payment,
    ];
  }
}

class Invoice extends Resource
{
    
    public $amount;
    public $due;
    public $taxId;
    public $name;
    public $fine;
    public $interest;
    public $discounts;
    public $tags;
    public $descriptions;
    public $pdf;
    public $link;
    public $nominalAmount;
    public $fineAmount;
    public $interestAmount;
    public $discountAmount;
    public $brcode;
    public $fee;
    public $status;
    public $transactionIds;
    public $created;
    public $updated;

    function __construct(array $params)
    {
        parent::__construct($params);

        $this->amount = Checks::checkParam($params, "amount");
        $this->due = Checks::checkDateTime(Checks::checkParam($params, "due"));
        $this->taxId = Checks::checkParam($params, "taxId");
        $this->name = Checks::checkParam($params, "name");
        $this->fine = Checks::checkParam($params, "fine");
        $this->interest = Checks::checkParam($params, "interest");
        $this->tags = Checks::checkParam($params, "tags");
        $this->descriptions = Checks::checkParam($params, "descriptions");
        $this->pdf = Checks::checkParam($params, "pdf");
        $this->link = Checks::checkParam($params, "link");
        $this->nominalAmount = Checks::checkParam($params, "nominalAmount");
        $this->fineAmount = Checks::checkParam($params, "fineAmount");
        $this->interestAmount = Checks::checkParam($params, "interestAmount");
        $this->discountAmount = Checks::checkParam($params, "discountAmount");
        $this->brcode = Checks::checkParam($params, "brcode");
        $this->fee = Checks::checkParam($params, "fee");
        $this->status = Checks::checkParam($params, "status");
        $this->transactionIds = Checks::checkParam($params, "transactionIds");
        $this->created = Checks::checkDateTime(Checks::checkParam($params, "created"));
        $this->updated = Checks::checkDateTime(Checks::checkParam($params, "updated"));
    
    }

    public static function resource()
    {
        $invoice = function ($array) {
            return new Invoice($array);
        };
        return [
            "name" => "Invoice",
            "maker" => $invoice,
        ];
    }
}


class TestRestGet
{    
    public function testGetPageSuccess()
    {
        list($transactions, $_cursor) = Rest::getPage(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            Transaction::resource(),
            "pt-BR",
            15,
            ["before" => "2022-02-01", "limit" => 1]
        );

        $transaction = $transactions[0];
        if (!is_int($transaction-> amount)){
            throw new Exception("failed");
        }
    }

    public function testGetContentSuccess()
    {
        list($invoices, $_cursor) = Rest::getPage(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            Invoice::resource(),
            "pt-BR",
            15,
            ["before" => "2022-02-01", "limit" => 1]
        );

        $id = $invoices[0] -> id;
        
        $content = Rest::getContent(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            Invoice::resource(),
            $id,
            "pdf",
            "pt-BR",
            15,
            null
        );

        $fp = fopen('invoice.pdf', 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    public function testGetSubResourceSuccess()
    {
        list($invoices, $_cursor) = Rest::getPage(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            Invoice::resource(),
            "pt-BR",
            15,
            ["before" => "2022-02-01", "limit" => 1]
        );

        $id = $invoices[0] -> id;
        
        
        $payment = function ($array) {
                return new Payment($array);
            };
        $subResource = [
            "name" => "Payment",
            "maker" => $payment
        ];

        $content = Rest::getSubResource(
            "0.0.0",
            StarkHost::bank,
            "v2",
            User::exampleProject(),
            Invoice::resource(),
            $id,
            $subResource,
            "pt-BR",
            15,
            null
        );

        print_r($content);

    }

}

echo "\n\nRest Get:";
$tests = new TestRestGet();

echo "\n\t- Get Page success";
$tests->testGetPageSuccess();
echo " - OK";

echo "\n\t- Get Content success";
$tests->testGetContentSuccess();
echo " - OK";

echo "\n\t- Get Sub Resource success";
$tests->testGetSubResourceSuccess();
echo " - OK";
