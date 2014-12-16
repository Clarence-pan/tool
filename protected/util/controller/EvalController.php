<?php


namespace tool\util\controller;
use tool\base\Controller;

class EvalController extends Controller{
    public function __construct(){
        $this->setTitle('EVAL');
    }

    public function doGetView($param){
        $this->render('eval', array('code' => $param['code']));
    }
    public function doPostEval($param){
        $this->render('eval', array('code' => $param['code']));
    }
} 