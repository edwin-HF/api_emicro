<?php


namespace annotation;


use EMicro\Request;
use service\auth\AuthToken;
use util\Response;

/**
 * @Annotation
 */
class Auth
{

    public function run(){

        try {

            $token = Request::header('token');

            if (empty($token))
                throw new \Exception('token is null');

            AuthToken::getInstance()->load($token);

        }catch (\Exception $exception){
            die(json_encode(Response::error($exception->getMessage(), Response::CODE_ERR_LOGIN)));
        }

    }

}