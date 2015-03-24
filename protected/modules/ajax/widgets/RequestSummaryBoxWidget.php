<?php


class RequestSummaryBoxWidget extends CWidget {
    /**
     * @var Request
     */
    public $request;

    public function run(){
        $this->render('request_summary_box');
    }
} 