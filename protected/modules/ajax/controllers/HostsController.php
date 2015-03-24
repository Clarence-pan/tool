<?php


class HostsController extends Controller{
    private $hostsFile = '';

    public function init(){
        parent::init();
        $this->layout = '/layout/main';
        $this->hostsFile = Yii::app()->params['hosts'];
    }

    public function actionIndex(){

        // 更新文件
        if (Yii::app()->request->requestType == 'POST'){
            $newContent = Yii::app()->request->getParam('content');
            file_put_contents($this->hostsFile, $newContent);
        }

        $this->render('index', array('fileName' => $this->hostsFile, 'fileContent' => file_get_contents($this->hostsFile)));
    }

    public function actionFormat(){
        // 更新文件
        if (Yii::app()->request->requestType == 'POST'){
            $content = Yii::app()->request->getParam('content');
            file_put_contents($this->hostsFile, $content);
        }

        $lines = explode("\n", file_get_contents($this->hostsFile));
        $this->render('format', array('fileName' => $this->hostsFile, 'lines' => $lines));
    }
} 