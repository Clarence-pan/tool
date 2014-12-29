<?php


namespace tool\util\controller;

use tool\base\Controller as Controller;

class MarkdownController extends Controller{
    public function __construct(){
        $this->setTitle('Markdown To HTML');
    }
    public function doPostToHtml($param){
        $this->render('markdown-to-html', array('html' => $this->toHtml($param['markdown'])));
    }

    private function toHtml($markdown){
        return $markdown;
    }
} 