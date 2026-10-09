<?php

namespace Test\Utf8;
use Exception;
use StarkCore\Utils\API;
use StarkCore\Utils\URL;
use StarkCore\Utils\SubResource;


class Label extends SubResource
{
    public $name;
    public $nothing = null;
    public $amount = 5;

    function __construct($name)
    {
        $this->name = $name;
    }
}


class TestUtf8
{
    const nonLatin1 = "Ol\u{E1} \u{2014} \u{201C}x\u{201D} \u{20AC}5 \u{1F600} \u{65E5}\u{672C}";

    public function testAsciiAndLatin1Unchanged()
    {
        $this->assertSame(["a" => "plain text 123"], API::castJsonToApiFormat(["a" => "plain text 123"]));
        $latin1 = "Ol\u{E1} \u{E7} \u{F1}";
        $this->assertSame(["a" => $latin1], API::castJsonToApiFormat(["a" => $latin1]));
    }

    public function testNonLatin1Preserved()
    {
        $text = self::nonLatin1;
        $this->assertSame(["a" => $text], API::castJsonToApiFormat(["a" => $text]));
        $this->assertSame(
            ["a" => ["b" => ["c" => $text], "d" => [$text]]],
            API::castJsonToApiFormat(["a" => ["b" => ["c" => $text], "d" => [$text]]])
        );
        $this->assertSame(
            ["a" => ["name" => $text, "amount" => 5]],
            API::castJsonToApiFormat(["a" => new Label($text)])
        );
    }

    public function testInvalidUtf8KeepsLegacyOutput()
    {
        // pins the behavior kept for invalid UTF-8: bad bytes become "?" and json_encode never fails
        $this->assertSame(
            ["a" => "Ol? ok ? fim", "b" => "??abc", "c" => "caf?"],
            API::castJsonToApiFormat(["a" => "Ol\xE1 ok \xC3 fim", "b" => "\xFF\xFEabc", "c" => "caf\xC3"])
        );
        $this->assertSame(true, json_encode(API::castJsonToApiFormat(["a" => "Ol\xE1 ok \xC3 fim"])) !== false);
    }

    public function testNullIntAndBoolUntouched()
    {
        $this->assertSame(
            ["b" => 7, "c" => true, "d" => false, "e" => 1.5],
            API::castJsonToApiFormat(["a" => null, "b" => 7, "c" => true, "d" => false, "e" => 1.5])
        );
    }

    public function testUrlEncode()
    {
        $this->assertSame(
            "?tags=caf%C3%A9%2C%E6%97%A5%E6%9C%AC%2C%F0%9F%98%80",
            URL::encode(["tags" => ["caf\u{E9}", "\u{65E5}\u{672C}", "\u{1F600}"]])
        );
        $this->assertSame("?limit=5&tags=a%2Cb", URL::encode(["limit" => 5, "tags" => ["a", "b"], "after" => null]));
    }

    private function assertSame($expected, $actual)
    {
        if ($expected === $actual) {
            return;
        }
        throw new Exception("expected " . var_export($expected, true) . ", got " . var_export($actual, true));
    }
}

echo "\n\nUtf8:";
$tests = new TestUtf8();

echo "\n\t- Ascii and Latin-1 unchanged";
$tests->testAsciiAndLatin1Unchanged();
echo " - OK";

echo "\n\t- Non Latin-1 preserved";
$tests->testNonLatin1Preserved();
echo " - OK";

echo "\n\t- Invalid UTF-8 keeps legacy output";
$tests->testInvalidUtf8KeepsLegacyOutput();
echo " - OK";

echo "\n\t- Null, int and bool untouched";
$tests->testNullIntAndBoolUntouched();
echo " - OK";

echo "\n\t- Url encode";
$tests->testUrlEncode();
echo " - OK";
