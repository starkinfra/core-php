<?php

namespace StarkCore\Utils;
use StarkCore\Utils\Checks;


class Rest
{
    public static function getPage($sdkVersion, $host, $apiVersion, $user, $resource, $language, $timeout, array $query = [])
    {
        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user,
            "GET", 
            API::endpoint($resource["name"]),
            null,
            $query,
            $apiVersion,
            $language,
            $timeout
        )->json();
        $entities = []; 
        foreach($json[API::lastNamePlural($resource["name"])] as $entity) {
            array_push($entities, API::fromApiJson($resource["maker"], $entity));
        }
        return [$entities, $json["cursor"]];
    }

    public static function getList($sdkVersion, $host, $apiVersion, $user, $resource, $language, $timeout, array $query = [])
    {
        $limit = Checks::CheckParam($query, "limit");
        $query["limit"] = is_null($limit) ? null : min($limit, 100);

        while (true) {
            $json = Request::fetch(
                $host,
                $sdkVersion,
                $user,
                "GET", 
                API::endpoint($resource["name"]),
                null,
                $query,
                $apiVersion,
                $language,
                $timeout
            )->json();
            $entities = $json[API::lastNamePlural($resource["name"])];
            
            foreach($entities as $entity) {
                yield API::fromApiJson($resource["maker"], $entity);
            }

            if (!is_null($limit)) {
                $limit -= 100;
                $query["limit"] = min($limit, 100);
            }

            if (!array_key_exists("cursor", $json)) {
                break;
            }

            $cursor = $json["cursor"];
            $query["cursor"] = $cursor;
            if (empty($cursor) | is_null($cursor) | (!is_null($limit) & $limit <= 0)) {
                break;
            }
        }
    }

    public static function getId($sdkVersion, $host, $apiVersion, $user, $resource, $id, $language, $timeout, $query = [])
    {
        $id = Checks::checkId($id);
        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user,
            "GET", 
            API::endpoint($resource["name"]) . "/" . $id,
            null,
            $query,
            $apiVersion,
            $language,
            $timeout
        )->json();
        $entity = $json[API::lastName($resource["name"])];
        return API::fromApiJson($resource["maker"], $entity);
    }

    public static function getContent($sdkVersion, $host, $apiVersion, $user, $resource, $id, $subresourceName, $language, $timeout, $options = null)
    {
        $id = Checks::checkId($id);
        $options = API::castJsonToApiFormat($options);
        $path = API::endpoint($resource["name"]) . "/" . $id . "/" . $subresourceName;
        return Request::fetch(
            $host,
            $sdkVersion,
            $user, 
            "GET",
            $path,
            null,
            $options,
            $apiVersion,
            $language,
            $timeout
        )->content;
    }

    public static function getSubresource($sdkVersion, $host, $apiVersion, $user, $resource, $id, $subresource, $language, $timeout, $options = null)
    {
        $id = Checks::checkId($id);
        $options = API::castJsonToApiFormat($options);
        $path = API::endpoint($resource["name"]) . "/" . $id . "/" . API::endpoint($subresource["name"]);
        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user, 
            "GET",
            $path,
            null,
            $options,
            $apiVersion,
            $language,
            $timeout
        )->json();
        $entity = $json[API::lastName($subresource["name"])];
        return API::fromApiJson($subresource["maker"], $entity);
    }

    public static function post($sdkVersion, $host, $apiVersion, $user, $resource, $entities, $language, $timeout, $query)
    {
        $entitiesJson = [];
        foreach($entities as $entity) {
            $entitiesJson[] = API::apiJson($entity, $resource["name"]);
        }
        $payload = [
            API::lastNamePlural($resource["name"]) => $entitiesJson
        ];

        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user, 
            "POST", 
            API::endpoint($resource["name"]),
            $payload,
            $query,
            $apiVersion,
            $language,
            $timeout
        )->json();

        $retrievedEntities = [];
        foreach($json[API::lastNamePlural($resource["name"])] as $entity) {
            $retrievedEntities[] = API::fromApiJson($resource["maker"], $entity);
        }

        return $retrievedEntities;
    }

    public static function postSingle($sdkVersion, $host, $apiVersion, $user, $resource, $entity, $language, $timeout)
    {
        $payload = API::apiJson($entity);
        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user, 
            "POST", 
            API::endpoint($resource["name"]), 
            $payload,
            null,
            $apiVersion,
            $language,
            $timeout
        )->json();
        $entityJson = $json[API::lastName($resource["name"])];
        return API::fromApiJson($resource["maker"], $entityJson);
    }

    public static function deleteId($sdkVersion, $host, $apiVersion, $user, $resource, $id, $language, $timeout)
    {
        $id = Checks::checkId($id);
        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user, 
            "DELETE", 
            API::endpoint($resource["name"]) . "/" . $id,
            null,
            null,
            $apiVersion,
            $language,
            $timeout
        )->json();
        $entity = $json[API::lastName($resource["name"])];
        return API::fromApiJson($resource["maker"], $entity);
    }

    public static function patchId($sdkVersion, $host, $apiVersion, $user, $resource, $id, $language, $timeout, $payload = [])
    {
        $id = Checks::checkId($id);
        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user, 
            "PATCH", 
            API::endpoint($resource["name"]) . "/" . $id, 
            API::castJsonToApiFormat($payload, $resource["name"]),
            null,
            $apiVersion,
            $language,
            $timeout
        )->json();
        $entity = $json[API::lastName($resource["name"])];
        return API::fromApiJson($resource["maker"], $entity);
    }

    public static function postRaw($sdkVersion, $host, $apiVersion, $user, $language, $timeout, $path, $payload, $query = null)
    {
        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user, 
            "POST", 
            $path,
            $payload,
            $query,
            $apiVersion,
            $language,
            $timeout,
        )->json();
        return $json;
    }

    public static function putMulti($sdkVersion, $host, $apiVersion, $user, $resource, $entities, $language, $query, $timeout)
    {
        $entitiesJson = [];
        foreach($entities as $entity) {
            $entitiesJson[] = API::apiJson($entity, $resource["name"]);
        }
        $payload = [
            API::lastNamePlural($resource["name"]) => $entitiesJson
        ];

        $json = Request::fetch(
            $host,
            $sdkVersion,
            $user, 
            "PUT", 
            API::endpoint($resource["name"]),
            $payload,
            $query,
            $apiVersion,
            $language,
            $timeout
        )->json();

        $retrievedEntities = [];
        foreach($json[API::lastNamePlural($resource["name"])] as $entity) {
            $retrievedEntities[] = API::fromApiJson($resource["maker"], $entity);
        }

        return $retrievedEntities;
    }
}
