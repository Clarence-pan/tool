<?php


class TestController extends Controller{
    public function actionSingleton(){
        TestSingleton::instance()->test();
    }
}


class TestSingleton extends Singleton{
    public function test(){
        echo 'test in '.__METHOD__;
    }
}