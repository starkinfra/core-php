<?php

namespace StarkCore\Utils;
use Exception;
use EllipticCurve\Ecdsa;
use StarkCore\Utils\URL;
use StarkCore\Environment;
use StarkCore\PublicUser;
use StarkCore\Error\InputErrors;
use StarkCore\Error\UnknownError;
use StarkCore\Error\InternalServerError;


class Response
{
    function __construct($status, $content)
    {
        $this->status = $status;
        $this->content = $content;
    }

    function json()
    {
        return json_decode($this->content, true);
    }
}


class Request
{
    public static function fetch($host, $sdkVersion, $user, $method, $path, $payload = null, $query = null, $apiVersion = "v2", $language="en-US", $timeout=15)
    {    
        $user = Checks::checkUser($user);
        $language = Checks::checkLanguage($language);

        $service = [
            StarkHost::infra => "starkinfra",
            StarkHost::bank => "starkbank",
            StarkHost::sign => "starksign"
        ][$host];

        $url = [
            Environment::production => "https://api.{$service}.com/",
            Environment::sandbox => "https://sandbox.api.{$service}.com/"
        ][$user->environment] . $apiVersion . "/";
        $url .= $path;
        if (!is_null($query)) {
            $url .= URL::encode($query);
        }

        $agent = "PHP-" . phpversion() . "-SDK-" . $host . "-" . $sdkVersion;

        $body = null;
        if (!is_null($payload))
            $body = json_encode($payload);

        $headers = [
            "User-Agent" => $agent,
            "Accept-Language" => $language,
            "Content-Type" => "application/json"
        ];
        $headers = array_merge($headers, self::_authenticationHeaders($user, $body));

        $response = Request::makeRequest($method, $headers, $url, $body);

        if ($response->status == 500) {
            throw new InternalServerError();
        }
        if ($response->status == 400) {
            throw new InputErrors($response->json()["errors"]);
        }
        if ($response->status != 200) {
            throw new UnknownError(strval($response->content));
        }

        return $response;
    }

    private static function makeRequest($method, $headers, $url, $body)
    {
        $stringHeader = "";
        foreach($headers as $key => $value) {
            $stringHeader .= $key . ": " . $value . "\r\n";
        }

        $opts = [
            'http' => [
                'method'  => $method,
                'header'  => $stringHeader,
                'ignore_errors' => true
            ]
        ];
        if (!is_null($body)) {
            $opts = [
                'http' => [
                    'method'  => $method,
                    'header'  => $stringHeader,
                    'content' => $body,
                    'ignore_errors' => true
                ]
            ];
        }
        
        try {
            $content = file_get_contents($url, false, stream_context_create($opts));
        } catch (Exception $e) {
            throw new UnknownError(strval($e));
        }

        $status = null;
        if (is_array($http_response_header)) {
            $parts = explode(' ', $http_response_header[0]);
            if (count($parts) > 1)
                $status = intval($parts[1]);
        }
        
        return new Response($status, $content);
    }

    private static function _authenticationHeaders($user, $body)
    {
        if ($user instanceof PublicUser)
            return [];

        $accessTime = strval(time());
        $message = $user->accessId() . ":" . $accessTime . ":" . $body;
        $signature = Ecdsa::sign($message, $user->privateKey())->toBase64();

        return [
            "Access-Id" => $user->accessId(),
            "Access-Time" => $accessTime,
            "Access-Signature" => $signature
        ];
    }
}
