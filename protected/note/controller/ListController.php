<?php


namespace tool\note\controller;
use tool\base\Controller as Controller;


class ListController extends Controller {
    public function doGetAll(){
        $this->render('list', array('title' => 'NOTE'));
    }
} 