<?php

namespace StarkCore\Utils;


class URL
{
    public static function encode($query)
    {
        $query = API::castJsonToApiFormat($query);
        $queryArray = [];
        foreach ($query as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if (is_iterable($value)) {
                $stringValue = [];
                foreach($value as $v) {
                    $stringValue[] = strval($v);
                }
                $value = join(",", $value);
            }
            if (is_bool($value)) {
                $value = $value ? "true" : "false";
            }
            $queryArray[$key] = $value;
        }

        if (count($queryArray) > 0) {
            return "?" . http_build_query($queryArray, '', '&', PHP_QUERY_RFC3986);
        }
        return "";
    }
}
