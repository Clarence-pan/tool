<?php


class ResponseBoxWidget extends CWidget{
    /**
     * @var Response
     */
    public $response;

    public function run(){
        $this->render('response_box');
    }
} 