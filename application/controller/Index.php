<?php


namespace controller;


use util\Helper;

/**
 * @Controller
 * @Route
 */
class Index
{

    /**
     * @View(welcome)!after
     */
    public function index(){
        return [['title' => '1212']];
    }

}