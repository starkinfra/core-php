<?php

namespace StarkCore\Utils;
use Exception;
use EllipticCurve\Ecdsa;
use StarkCore\Utils\Cache;
use EllipticCurve\PublicKey;
use EllipticCurve\Signature;
use StarkCore\Error\InvalidSignatureError;


class Parse
{
    public static function parseAndVerify($content, $signature, $sdkVersion, $apiVersion, $host, $resource, $user, $language, $timeout)
    {
        $content = Self::verify($content, $signature, $sdkVersion, $apiVersion, $host, $user, $language, $timeout);
        $json = json_decode($content, true);
        $entity = $json;
        if ($resource["name"] == "Event") {
            $entity = $json[API::lastName($resource["name"])];
        }

        return API::fromApiJson($resource["maker"], $entity);
    }

    public static function verify($content, $signature, $sdkVersion, $apiVersion, $host, $user, $language, $timeout)
    {
        try {
            $signature = Signature::fromBase64($signature);
        } catch (Exception $e) {
            throw new InvalidSignatureError("The provided signature is not valid");
        }

        $publicKey = self::getPublicKey(
            $sdkVersion,
            $host,
            $apiVersion,
            $user,
            $language,
            $timeout,
            True
        );
        if (self::verifySignature($content, $signature, $publicKey)) {
            return $content;
        }

        throw new InvalidSignatureError("The provided signature and content do not match the Stark public key");
    }

    private static function verifySignature($content, $signature, $publicKey)
    {
        return Ecdsa::verify($content, $signature, $publicKey);
    }

    private static function getPublicKey($sdkVersion, $host, $apiVersion, $user, $language, $timeout, $refresh=false)
    {
        $publicKey = Cache::getStarkPublicKey();
        if (!(is_null($publicKey)) and !($refresh))
            return $publicKey;

        $pem = Request::fetch(
            $host,
            $sdkVersion,
            $user,
            "GET",
            "/public-key",
            null,
            ["limit" => 1],
            $apiVersion,
            $language,
            $timeout
        )->json()["publicKeys"][0]["content"];
        $publicKey = PublicKey::fromPem($pem);
        Cache::setStarkPublicKey($publicKey);
        return $publicKey;
    }
}
