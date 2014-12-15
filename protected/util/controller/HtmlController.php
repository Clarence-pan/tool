<?php


namespace tool\util\controller;
use tool\base\Controller as Controller;

class HtmlController extends Controller {
    public function __construct(){
        $this->setTitle('View HTML');
    }
    public function doPostView($param){
        $this->render('view_html', array('html' => $param['html']));
    }
    public function doGetView($param){
        $this->render('form');
    }
} 