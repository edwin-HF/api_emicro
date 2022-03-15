<?php


namespace util;


class Media
{

    public static function image($url){

        if (empty($url))
            return '';

        if (strpos($url,'http') === 0)
            return $url;

        return config('system.media_domain') . '/data/' . $url;

    }

    public static function mediaCdn(){
        return config('system.media_domain') . '/data/';
    }

}