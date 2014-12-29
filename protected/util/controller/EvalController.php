<?php


namespace tool\util\controller;
use tool\base\Controller;

class EvalController extends Controller{
    public function __construct(){
        $this->setTitle('EVAL');
    }

    public function doGetView($param){
        return $this->doPostEval($param);
    }
    public function doPostView($param){
        return $this->doPostEval($param);
    }
    public function doPostEval($param){
        return $this->render('eval', array('code' => $param['code']));
    }
} 