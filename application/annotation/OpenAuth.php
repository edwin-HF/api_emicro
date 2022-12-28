<?php

namespace annotation;


use EMicro\Request;
use service\auth\AuthToken;
use util\Response;

/**
 * @Annotation
 */
class OpenAuth
{

    public function run(){

        try {

            $token = Request::header('token');

            if (empty($token))
                throw new \Exception('token is null');

            AuthToken::getInstance()->load($token);

        }catch (\Exception $exception){}

    }

}