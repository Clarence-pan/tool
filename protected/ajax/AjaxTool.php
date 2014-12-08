<?php

class AjaxTool{
    private $view;
    private $historyManager;
    public function __construct(){
        $this->view = new AjaxView();
        $this->historyManager = new AjaxHistoryManager();
    }
    public function run($action, $params=null){
        $action = 'action'.$action;
        $this->$action($params);
    }
    public function render($viewName, $params=null){
        $render = 'render'.$viewName;
        return $this->view->$render($params);
    }
    public function renderResult($result){
        return $this->render('result', $result);
    }
    public function actionIndex($params){
        $this->render('index', array(
            'url' => $params['url'],
            'params' => $params['params']
        ));
    }
    public function actionAddHistory($params){
        $this->historyManager->add(new AjaxHistory(array(
            'request' => new AjaxRequest($params['request']),
            'response' => new AjaxResponse($params['response'])
        )));
        $this->renderResult("OK");
    }
    public function actionClearHistory($params){
        $this->historyManager->clear();
        $this->renderResult('OK');
    }
    public function recordHistory(){

    }
}
