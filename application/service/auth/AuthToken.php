<?php


namespace service\auth;


use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;

use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use util\Response;


class AuthToken
{

    private function __construct(){}
    private function __clone(){}

    private static $instance = null;
    /**
     * @var Configuration
     */
    private static $config  = null;
    private $jwt_token = '';

    public static function getInstance(){

        if (!is_null(self::$instance))
            return self::$instance;

        return self::$instance = ((new self())->init());
    }

    // AES CIPHER
    const CIPHER = "AES-128-CBC";

    private $user_data = [];

    /**
     * set UserData
     * @param $key
     * @param $value
     * @return AuthToken
     * @author Edwin Fan
     */
    public function setUserData($key,$value){
        $this->user_data[$key] = $value;
        return $this;
    }

    /**
     * get UserData
     * @param string $key
     * @return mixed|string
     * @author Edwin Fan
     */
    public function getUserData($key){
        return isset($this->user_data[$key]) ? $this->user_data[$key] : '';
    }

    public function getAllData(){
        return $this->user_data;
    }

    /**
     * get token
     * @return string
     * @throws \Exception
     */
    public function obtainToken(){

        $dateTime = new \DateTimeImmutable();
        $expireAt = $dateTime->add(new \DateInterval(sprintf('PT%sM',config('auth.jwt_ttl'))));

        return self::$config->builder()
            ->issuedBy(config('auth.jwt_issue'))
            ->expiresAt($expireAt)
            ->issuedAt($dateTime)
            ->identifiedBy(uniqid())
            ->withClaim('data',$this->aesEncode(json_encode($this->user_data)))
            ->getToken(self::$config->signer(),self::$config->signingKey())->toString();

    }

    /**
     * refresh token
     * @throws \Exception
     * @author Edwin Fan
     */
    public function refreshToken(){

        $dateTime = new \DateTimeImmutable();
        $expireAt = $dateTime->add(new \DateInterval(sprintf('PT%sM',config('auth.jwt_refresh_ttl'))));

        return self::$config->builder()
            ->issuedBy(config('auth.jwt_issue'))
            ->expiresAt($expireAt)
            ->issuedAt($dateTime)
            ->identifiedBy(uniqid())
            ->getToken(self::$config->signer(),self::$config->signingKey())->toString();

    }

    /**
     * load
     * @param $jwtToken
     * @return AuthToken
     * @throws \Exception
     */
    public function load($jwtToken){

        $this->jwt_token = $jwtToken;

        /**
         * @var $payload DataSet
         */
        $payload = $this->parseToken();

        try {

            if (!$payload->has('data'))
                throw new \Exception('no data');

            $user_data_json = $this->aesDecode($payload->get('data'));

            if (empty($user_data_json))
                throw new \Exception('illegal data');

            $this->user_data = json_decode($user_data_json,true);

        }catch (\Exception $exception){

        }

        return $this;

    }


    /**
     * init
     * @return $this
     * @throws \Exception
     */
    private function init(){

        if (is_null(self::$config)){
            self::$config = Configuration::forSymmetricSigner(new Sha256(),InMemory::plainText(config('auth.jwt_secret')));
            self::$config->setValidationConstraints(
                new ValidAt(SystemClock::fromSystemTimezone(),new \DateInterval(sprintf('PT%sM',config('auth.jwt_ttl')))),
                new IssuedBy(config('auth.jwt_issue')),
                new SignedWith(self::$config->signer(),self::$config->signingKey())
            );

        }

        return $this;
    }

    /**
     * parse token
     * @return array|string
     * @throws \Exception
     * @author Edwin Fan
     */
    private function parseToken(){

        if (empty($this->jwt_token))
            throw new \Exception('token is null',Response::CODE_ERR_LOGIN);

        try {
            /**
             * @var $token Plain
             */
            $token = self::$config->parser()->parse($this->jwt_token);

            if (!self::$config->validator()->validate($token,...self::$config->validationConstraints()))
                throw new \Exception('Token已失效');

            return $token->claims();

        }catch (\Exception $exception){
            throw $exception;
        }

    }

    /**
     * encode
     * @param $str
     * @return string
     */
    private function aesEncode($str){

        $iv_len = openssl_cipher_iv_length(self::CIPHER);
        $iv     = openssl_random_pseudo_bytes($iv_len);

        $raw  = openssl_encrypt($str, self::CIPHER,  config('auth.aes_key'), OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $raw,  config('auth.aes_key'), $as_binary=true);

        return base64_encode( $iv . $hmac . $raw );
    }

    /**
     * decode
     * @param $str
     * @return bool|false|string
     */
    private function aesDecode($str){

        $c = base64_decode($str);
        $iv_len = openssl_cipher_iv_length(self::CIPHER);
        $iv     = substr($c, 0, $iv_len);

        $hmac   = substr($c, $iv_len, $sha2len=32);
        $raw    = substr($c, $iv_len + $sha2len);

        $original = openssl_decrypt($raw, self::CIPHER,  config('auth.aes_key'), OPENSSL_RAW_DATA, $iv);
        $cmac     = hash_hmac('sha256', $raw,  config('auth.aes_key'), $as_binary=true);

        if (!hash_equals($hmac, $cmac))
            return false;

        return $original;

    }

}