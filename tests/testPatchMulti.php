<?php

namespace Test\PatchMulti;
use Exception;

use Test\Utils\User;
use StarkCore\Utils\Rest;
use StarkCore\Utils\StarkHost;
use StarkCore\Error\UnknownError;


class Card
{
    public $id;
    public $holderName;
    public $tags;

    function __construct($id, $holderName = null, $tags = null)
    {
        $this->id = $id;
        $this->holderName = $holderName;
        $this->tags = $tags;
    }

    function __toArray()
    {
        return get_object_vars($this);
    }
}


// Only the outgoing HTTP call is replaced (an https stream wrapper records the request): signing, headers and payload
// building run for real. The wrapper cannot set $http_response_header, which the core reads for the status, so the core
// reports the answer as UnknownError after the request was sent; this test therefore covers the request half of patchMulti,
// and the response half is covered by the SDK tests that run against the sandbox.
class RecordingHttps
{
    public static $requests = [];
    public $context;
    private $position = 0;
    private $content = '{"cards": []}';

    public function stream_open($path, $mode, $options, &$openedPath)
    {
        $http = stream_context_get_options($this->context)["http"];
        self::$requests[] = ["url" => $path, "method" => $http["method"], "header" => $http["header"], "body" => $http["content"] ?? null];
        return true;
    }

    public function stream_read($count)
    {
        $chunk = substr($this->content, $this->position, $count);
        $this->position += strlen($chunk);
        return $chunk;
    }

    public function stream_eof()
    {
        return $this->position >= strlen($this->content);
    }

    public function stream_stat()
    {
        return [];
    }
}


class TestPatchMulti
{
    private function patchMultiRecording($entities, $query = null)
    {
        RecordingHttps::$requests = [];
        stream_wrapper_unregister("https");
        stream_wrapper_register("https", RecordingHttps::class);
        try {
            Rest::patchMulti(
                "0.0.0",
                StarkHost::infra,
                "v2",
                User::exampleProject(),
                ["name" => "Card", "maker" => function ($array) { return new Card($array["id"]); }],
                $entities,
                "en-US",
                $query,
                15
            );
        } catch (UnknownError $error) {
            // expected: see the note above RecordingHttps
        } finally {
            stream_wrapper_restore("https");
        }
        if (count(RecordingHttps::$requests) != 1) {
            throw new Exception("expected exactly one request, got " . count(RecordingHttps::$requests));
        }
        return RecordingHttps::$requests[0];
    }

    public function testSendsPatchWithPluralPayload()
    {
        $request = $this->patchMultiRecording([
            new Card("1", "Tony Stark"),
            ["id" => "2", "holderName" => "Tony Stark"],
        ]);
        if ($request["method"] != "PATCH") {
            throw new Exception("method should be PATCH, got " . $request["method"]);
        }
        if (!str_ends_with($request["url"], "/v2/card")) {
            throw new Exception("unexpected url " . $request["url"]);
        }
        $expected = ["cards" => [["id" => "1", "holderName" => "Tony Stark"], ["id" => "2", "holderName" => "Tony Stark"]]];
        if (json_decode($request["body"], true) != $expected) {
            throw new Exception("unexpected body " . $request["body"]);
        }
        if (strpos($request["header"], "Access-Signature: ") === false || strpos($request["header"], "Content-Type: application/json") === false) {
            throw new Exception("missing signature or content type header");
        }
    }

    public function testUnsetAttributesAreNotSent()
    {
        $request = $this->patchMultiRecording([new Card("1", null, ["x"])]);
        if (json_decode($request["body"], true) != ["cards" => [["id" => "1", "tags" => ["x"]]]]) {
            throw new Exception("null attributes must not be sent: " . $request["body"]);
        }
    }

    public function testQueryParametersAreSentInTheUrl()
    {
        $request = $this->patchMultiRecording([new Card("1")], ["expand" => ["rules"]]);
        if (!str_ends_with($request["url"], "/v2/card?expand=rules")) {
            throw new Exception("query not in the url: " . $request["url"]);
        }
    }
}

$test = new TestPatchMulti();
$test->testSendsPatchWithPluralPayload();
$test->testUnsetAttributesAreNotSent();
$test->testQueryParametersAreSentInTheUrl();
echo "\n\nPatchMulti:\n\t- request payload, null omission and query - OK\n";
