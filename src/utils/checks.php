<?php

namespace StarkCore\Utils;
use Exception;
use \DateTime;
use \DateInterval;
use AssertionError;
use StarkCore\Environment;
use EllipticCurve\PrivateKey;
use StarkCore\Utils\StarkDate;
use StarkCore\Utils\StarkDateTime;


class Checks
{
    public static function checkParam(&$params, $key)
    {
        $var = null;
        if (isset($params[$key])) {
            $var = $params[$key];
            unset($params[$key]);
        }
        return $var;
    }

    public static function checkParams($params)
    {
        $checkParams = true;
        if (array_key_exists("checkParams", $params)) {
            $checkParams = $params["checkParams"];
            unset($params["checkParams"]);
        }

        if ($checkParams & count($params) > 0) {
            throw new Exception("unknown parameters: " . join(", ", array_keys($params)));
        }
    }

    public static function checkId($id)
    {
        $id = strval($id);
        if (strlen($id) == 0)
            throw new Exception("invalid id: " . $id);
        return $id;
    }

    public static function checkEnvironment($environment)
    {
        if (!Environment::isValid($environment))
            throw new Exception("Select a valid environment:  " . join(", ", (Environment::values())));
        return $environment;
    }

    public static function checkPrivateKey($pem)
    {
        try {
            PrivateKey::fromPem($pem);
        } catch (Exception $e) {
            throw new Exception("Private-key must be valid secp256k1 ECDSA string in pem format");
        }
        return $pem;
    }

    public static function checkDateTime($data)
    {
        if (is_null($data))
            return $data;
        if ($data instanceof DateTime)
            return $data;
        return new DateTime(strval($data));
    }

    public static function checkDateTimeOrDate($date)
    {
        if (is_null($date))
            return $date;
        if ($date->format('H:i:s') == "00:00:00") {
            return new StarkDate($date);
        }
        return new StarkDateTime($date);
    }

    public static function checkDateInterval($data)
    {
        if (is_null($data))
            return $data;
        if ($data instanceof DateInterval)
            return $data;
        if (is_int($data))
            return new DateInterval('PT0H0M' . $data . "S");
        return new DateInterval($data);
    }

    public static function checkUser($user)
    {
        if (is_null($user))
            throw new AssertionError("A user is required to access our API. Check our README: https://github.com/starkinfra/core-php/");
        return $user;        
    }

    public static function checkLanguage($language)
    {
        $acceptedLanguages = ["en-US", "pt-BR"];
        if (!in_array($language, $acceptedLanguages)) {
            throw new AssertionError("Language must be one from " . join(", ", $acceptedLanguages));
        }
        return $language;
    }
}
