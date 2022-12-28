<?php

namespace util;

class Log
{

    public static function logFormat($content, $type, $header = []){

        try {

            $dir = BASE_PATH . '/logs/' . $type . '/csv';
            $filename = sprintf('%s/%s.csv', $dir, date('Y-m-d'));

            if (!empty($content)){

                if (!is_dir($dir)){
                    @mkdir($dir,0777, true);
                }

                $recordHeader = false;
                if (!file_exists($filename)){
                    $recordHeader = true;
                }
                $fp = @fopen($filename, 'a+');

                if ($recordHeader && !empty($header)){
                    @fputcsv($fp, $header);
                }
                @fputcsv($fp, $content);
                @fclose($fp);

            }

        }catch (\Exception $exception){}
    }

    public static function write($data, $prefix=''){

        try {

            $dir = BASE_PATH . '/logs/' . $prefix;
            $filename = sprintf('%s/%s.log', $dir, date('Y-m-d'));

            if (!is_dir(dirname($filename))){
                @mkdir(dirname($filename),0777, true);
            }

            $logFormat = sprintf("【%s】%s data : %s %s", date('Y-m-d H:i:s'), PHP_EOL, json_encode($data), PHP_EOL);
            file_put_contents($filename, $logFormat, FILE_APPEND);
        }catch (\Throwable $exception){}

    }

}