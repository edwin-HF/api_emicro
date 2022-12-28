<?php

namespace annotation;

use cache\provider\SafeUnique;
use EMicro\Config;
use EMicro\Dispatcher;
use EMicro\Request;
use model\AccessRule;
use service\auth\Sign;
use util\Helper;
use util\Log;

/**
 * @Annotation
 */
class Safe
{

    public function run(){

        try {

            $params = Request::input();

            if (!isset($params['timestamp']) || !isset($params['unique']) || !isset($params['sign']))
                throw new \Exception('非法请求1', 4001);

            if (time() - $params['timestamp'] > 5 * 60)
                throw new \Exception('非法请求2');

            if (!SafeUnique::newInstance()->verify($params['unique'], $params['timestamp']))
                throw new \Exception('非法请求3');

            $clientSign = $params['sign'];

            if (empty($clientSign))
                throw new \Exception('非法请求');

            unset($params['sign']);
            $appSecret = Config::get('system.app_secret', '');

            if ($clientSign != Sign::signature($params, $appSecret))
                throw new \Exception('非法请求');

        }catch (\Exception $exception){

            $ip = Helper::getClientIp();

            $isForbid = AccessRule::query()->getInfo(
                [
                    ['ip', '=', $ip]
                ]
            );

            if (empty($isForbid)){
                AccessRule::query()->insert(
                    [
                        'ip'         => $ip,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]
                );

                \cache\provider\AccessRule::newInstance()->clean();
            }

            Log::logFormat([
                $ip,
                Dispatcher::getInstance()->uri(),
                $exception->getMessage(),
                json_encode(Request::input()),
                date('Y-m-d H:i:s')
            ],'invade');
            die($exception->getMessage());
        }

    }

}