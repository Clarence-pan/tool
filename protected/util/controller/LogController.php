<?php


namespace tool\util\controller;

use tool\base\Controller as Controller;

class LogController extends Controller {

    public function __construct(){
        $this->setTitle('View LOG');
        $this->setLayout(null);
    }

    public function doGetView(){
        $this->render('log');
    }

    public function doPostView(){
        $this->render('log');
    }
} 