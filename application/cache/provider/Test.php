<?php

namespace cache\provider;

use cache\CacheDriver;
use Edv\Cache\Strategy\CacheList;

class Test extends CacheList
{

    use CacheDriver;

    public function cacheKey()
    {
        return sprintf('%s:%s', $this->cacheKeyPrefix(), $this->param('param'));
    }

    public function patch()
    {
        return [1,2,3];
    }

}