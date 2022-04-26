<?php


namespace cache;


use Edv\Cache\Driver\Traits\AutoGenerateCacheKey;
use Edv\Cache\Driver\Traits\LocalConfig;

trait CacheDriver
{

    use AutoGenerateCacheKey;
    use LocalConfig;

    public function expire(){
        return 60 *5;
    }

}