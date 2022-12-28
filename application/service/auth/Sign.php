<?php


namespace service\auth;


class Sign
{
    /**
     * @param array $params
     * @param $secret
     * @return string
     * @author Edwin Fan
     */
    public static function signature($params, $secret){
        return self::sign(self::arr2str($params), $secret);
    }

    /**
     * sign
     * @author Edwin Fan
     * @param $body
     * @param $secret
     * @return string
     */
    private static function sign($body,$secret){
        return hash_hmac('sha256',$body,$secret);
    }

    /**
     * arr2str
     * @author Edwin Fan
     * @param $arr
     * @param array $exclude
     * @return string
     */
    private static function arr2str($arr,$exclude = []){

        if (empty($arr)) return '';

        ksort($arr);

        $trans_arr = [];
        foreach ($arr as $key => $value){

            if (in_array($key,$exclude))
                continue;

            $trans_arr[] = "{$key}={$value}";

        }

        return implode('&',$trans_arr);

    }

}